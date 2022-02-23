<?php
require_once 'php-binance-api.php';
require_once 'elementaryFunction.php';
require_once 'economicFunction.php';
ini_set('trader.real_precision', '9');

//CONST PATH='./../fileJson/';// da file Bat
// CONST PATH='./fileJson/';// da file PHP/shell
$path='./fileJson/';//da file PHP/shell
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'  & (php_sapi_name() == 'cli') ) {
    $path='./../fileJson/';// da file Bat
}

function newBinance($file){
    global $path;
    $file=$path.$file.'.json';
    return new Binance\API($file);
}

$percVenditaPerdita=0;
$percVenditaVincita=100;
$monetaDaGioco='EUR';
$MinPercentualeDiCrescita=0;
$moltiplicatore=1;
$timeMaxForControl=5;
$quantityMoltiplicator=1;

function initConfiguration(){
    $config=leggiFileJson("config");
    global $percVenditaPerdita;
    global $percVenditaVincita;
    global $monetaDaGioco;
    global $MinPercentualeDiCrescita;
    global $moltiplicatore;
    global $timeMaxForControl;
    global $count;
    global $quantityMoltiplicator;
    global $token;
    global $chatId;
    global $test;
    global $temporalView;
    $percVenditaPerdita=$config->percVenditaPerdita/100;
    $moltiplicatore=$config->moltiplicatore;
    $percVenditaVincita=$config->percVenditaVincita;
    $monetaDaGioco =$config->monetaDaGioco;
    $MinPercentualeDiCrescita=$config->MinPercentualeDiCrescita;
    $timeMaxForControl=$config->timeMaxForControl;
    $quantityMoltiplicator=$config->quantityMoltiplicator;
    $token=$config->token;
    $chatId=$config->chatId;
    $count=1;
    $temporalView=$config->temporalView;
    $test=$config->test;
}


function HandleInit($api){
    global $count;
    global $moltiplicatore;
    $simbolInSchedule=leggiFileJson('simbolbuyed');
    if($simbolInSchedule){
        SellSymbol((array)$simbolInSchedule,$api);
    }else{
        global $monetaDaGioco;
        global $MinPercentualeDiCrescita;
                                // $datarrayPrima=leggiFileJson('updatingPrice');
        $datarrayPrima=leggiUpdatePriceControll($api);
        $dataarrayDopo= $api->prices();
        if(!($count>1)){
            $dataarrayDopo= scriviUpdatePriceControll($api);
        }
        if($datarrayPrima){
        $symbolChosen=MaxVarPercSymbolFrom2Array((array)$datarrayPrima,(array)$dataarrayDopo);
            if($symbolChosen['VarPerc']>=$MinPercentualeDiCrescita){
                $SymbolFeatures=GetSymbolFeatures($symbolChosen,$api);
                $symbol=$symbolChosen['symbol'];
                $SymbolFeatures['VarPerc']=$symbolChosen['VarPerc'];
                $SymbolFeatures['prezzoFinale']=$symbolChosen['prezzoFinale'];
                $LastPriceViewd=(float)$SymbolFeatures['LastPriceViewd'];
                $datarrayPrima=(array)$datarrayPrima;
                $pricesymbol=(float)$datarrayPrima[$symbol];
                if($LastPriceViewd>$pricesymbol){
                    $result=BuySymbol($SymbolFeatures,$api);
                    if(($moltiplicatore>1) && ($count<=$moltiplicatore) && ($result==true)){
                        echo PHP_EOL.PHP_EOL."Moltiplicatore: $moltiplicatore, Contatore: $count ".PHP_EOL.PHP_EOL;
                        $count++;
                        HandleInit($api);
                    }
                }
            }else{
                echo PHP_EOL.PHP_EOL."in seeking for other naive: ".$symbolChosen['symbol']." VarPerc: ".$symbolChosen['VarPerc'].PHP_EOL.PHP_EOL;
            }
        }
        
    }
}



function BuySymbol($SymbolFeatures,$api){
    $SymbolFeatures=CalculateQuantity($SymbolFeatures,$api);
    if(!$SymbolFeatures){return false;}
    $boolControll=controllToBuy($SymbolFeatures,$api);
    if($boolControll){
         $order = $api->buy($SymbolFeatures["symbol"], $SymbolFeatures['quantityToBuy'], "","MARKET");
    }else{
        echo PHP_EOL.PHP_EOL."down price ... seeking for other naive: ".$SymbolFeatures['symbol']." VarPerc: ".$SymbolFeatures['VarPerc']." highPrice: ".$SymbolFeatures['prev5min']['high'].PHP_EOL.PHP_EOL;
    }
    $symbolToBuy=false;
    if(isset($order['status']) && $order['status']=='FILLED'){
        $SymbolFeatures['executedQty']=(float)$order['executedQty'];
        $SymbolFeatures['OrderBuy']=$order['fills'];
        $symbolToBuy=true;
        writeInJson('simbolbuyed',$SymbolFeatures);
        // usleep(250000);
        return SellSymbol($SymbolFeatures,$api);
    }
    return false;
}


function SellSymbol($SymbolFeatures,$api){
    $SymbolFeatures=CalculatePriceAndQtyToSell($SymbolFeatures,$api);
    writeInJson('simbolbuyed',$SymbolFeatures);
    $order = $api->sell($SymbolFeatures['symbol'], $SymbolFeatures['quantityForSell'], $SymbolFeatures['prezzoVendita'],"LIMIT"); 
    if(isset($order['status'])){
        writeInJson('simbolbuyed','');
        $arraySchedule=leggiFileJson('simbolScheduled');
        $arraySchedule[]=$SymbolFeatures;
        writeInJson('simbolScheduled',$arraySchedule);
        writeInJson('updatingPrice','');
        global $chatId;
        sendMessage($chatId,"VENDUTO IN LIMIT ".$SymbolFeatures['symbol']." A ".$SymbolFeatures['prezzoVendita']);
        echo PHP_EOL."VENDUTO IN LIMIT ".$SymbolFeatures['symbol']." A ".$SymbolFeatures['prezzoVendita'].PHP_EOL.PHP_EOL ;
        return true; 
    }
    // else{
    //     $SymbolFeatures['countError']=(float)$SymbolFeatures['countError']+1;
    //     writeInJson('simbolbuyed',$SymbolFeatures);
    //     SellSymbol($SymbolFeatures,$api);
    // }
    return false;
}


//no yet in function  //attenzione ad array in if(array) restituisce true
function updateSymbolBuyedCount(){
    $arraySchedule=leggiFileJson('simbolbuyed');
    $arraySchedule->countTryForSell++;
    writeInJson('simbolbuyed',$arraySchedule);
}

//attenzione ad array nella condizione if(datarrayPrima)
function leggiUpdatePriceControll($api){
    global $timeMaxForControl;
    $datarrayPrima= leggiFileJson('updatingPrice');
    $result=$datarrayPrima;
    if($datarrayPrima){
        $datarrayPrima=(array)$datarrayPrima;
        $ArrayData=(array)$datarrayPrima['ServerTime'];
        $dt = new DateTime();
        $interval=diffFrom2DateinMinute($ArrayData['date'],$dt->format('Y-m-d H:i:s.u'));
        if($interval>$timeMaxForControl){scriviUpdatePriceControll($api);$result=false;}
    }
    return $result;
}

function scriviUpdatePriceControll($api){
    $dt = new DateTime();
    $dataarrayDopo= $api->prices();
    $dataarrayDopo['ServerTime']=$dt;
    writeInJson('updatingPrice',$dataarrayDopo);
    return $dataarrayDopo;
}




function controllToBuy(&$SymbolFeatures,$api){
    $SymbolFeatures["diffVar"]=VariazioneDiDifferenza((float)$SymbolFeatures['LastPriceViewd'],(float)$SymbolFeatures['prezzoFinale']);
    $SymbolFeatures['prevDay']=$api->prevDay($SymbolFeatures["symbol"]);
    $SymbolFeatures['prev5min']=getLastCandle($api,$SymbolFeatures["symbol"],'5m');
    $percChangePrice=(float)$SymbolFeatures['prev5min']['priceChangePercent'];
    $highPriceDay=(float)$SymbolFeatures['prevDay']['highPrice'];
    $bidPrice=(float)$SymbolFeatures['prevDay']['bidPrice'];
    $askPrice=(float)$SymbolFeatures['prevDay']['askPrice'];
    $lastPrice=(float)$SymbolFeatures['prevDay']['lastPrice'];
    $percBid=($bidPrice*100)/$lastPrice;
    $SymbolFeatures['prevDay']['percAsk']=($askPrice*100)/$lastPrice;
    $SymbolFeatures['prevDay']['percBid']=$percBid;
    $SymbolFeatures['prevDay']['openTime']=handleDate($SymbolFeatures['prevDay']['openTime']);
    $SymbolFeatures['prevDay']['closeTime']=handleDate($SymbolFeatures['prevDay']['closeTime']);


    $checkQtyToVolForBuy= (($SymbolFeatures['prev5min']['volume']-$SymbolFeatures['prev5min']['takerBuyBaseAssetVolume'])+$SymbolFeatures['quantityToBuy'])<((float)$SymbolFeatures['prevDay']['askQty']);

        $condition0=($SymbolFeatures['prezzoFinale']<=(float)$SymbolFeatures['LastPriceViewd'] );
        $condition1=($SymbolFeatures['prezzoFinale']<=(float)$SymbolFeatures['prev5min']['close'] );
        $condition2=((float)$SymbolFeatures['prevDay']['lastPrice']<=(float)$SymbolFeatures['prevDay']['askPrice']);
        $condition3=((float)$SymbolFeatures['prev5min']['close']>(float)$SymbolFeatures['prev5min']['open']);

    // $prevPriceSell=calculatePrevPriceSell((float)$SymbolFeatures['prevDay']['lastPrice'],$SymbolFeatures);
        $condition4=($percBid>=99.60);
        $condition5= ($SymbolFeatures['prev5min']['volumeChangePercent']>0);
        $condition6= ($SymbolFeatures['prev5min']['priceChangePercent']>0);
        $condition7=($lastPrice<$highPriceDay);
        $condition8=((float)$SymbolFeatures['prev5min']['prev']['close']<(float)$SymbolFeatures['prev5min']['prev']['open']);

        //condition
        //         
        //          (float)$SymbolFeatures['LastPriceViewd']<=$bidPrice
        //         ($lastPrice<$SymbolFeatures['prev5min']['high'])
        //         ((float)$SymbolFeatures['prevDay']['askPrice']<$prevPriceSell)

        //         ($checkQtyToVolForBuy)
        /////end
        $message=PHP_EOL.PHP_EOL.
        "prezzoFinale <= LastPriceViewd ".(int)$condition0.PHP_EOL.
        "prezzoFinale <= prev5min close".(int)$condition1.PHP_EOL.
        "prevDay lastPrice <= prevDay askPrice".(int)$condition2.PHP_EOL.
        "prev5min close > prev5min open ".(int)$condition3.PHP_EOL.
        "percBid>=99.60 ".(int)$condition4.PHP_EOL.
        "'prev5min volumeChangePercent >0 ".(int)$condition5.PHP_EOL.
        "prev5min priceChangePercent >0 ".(int)$condition6.PHP_EOL.
        "checkQtyToVolForBuy ".(int)$checkQtyToVolForBuy.PHP_EOL.
        "".PHP_EOL.PHP_EOL ;
        // if($condition6 && $condition5){sendMessage($chatId,$message);}
        $condition=($condition8&&$condition1&&$condition2&&$condition3&&$condition4&&$condition5&&$condition6&&$condition7);
        // if($condition){
        //     $SymbolFeatures['prev1min']=getLastCandle($api,$SymbolFeatures["symbol"],'1m');
        //     $condition7=($SymbolFeatures['prev1min']['volumeChangePercent']>0);
        //     $condition8=($SymbolFeatures['prev1min']['priceChangePercent']>0);
        //     global $chatId;
        //     sendMessage($chatId,$SymbolFeatures['prev1min']['volumeChangePercent']." and ".$SymbolFeatures['prev1min']['priceChangePercent']);
        //     return $condition7&&$condition8;
        // }
        return $condition;
}

function controllToBuy2(&$SymbolFeatures,$api){
    
    $SymbolFeatures['prev5min']=getLastCandle($api,$SymbolFeatures["symbol"],'5m');
//prima candela rossa, con il close vicino alla media Media e sotto alla media Piccola.
// seconda candela verde, con il close maggiore della media Media e maggiore del close precedente.
//verifica del trend che sia valido almeno degli ultimi 10 volori per ogni medie, che non si incrociano, 
//e che sia un mercato rialzista.

$PrimaCandela=$SymbolFeatures['prev5min'];
$SecondaCandela=$SymbolFeatures['prev5min']['prev'];
//Rossa && Verde:
if($PrimaCandela['open']>$PrimaCandela['close'] && $SecondaCandela['close']>$SecondaCandela['open']){
    $arrayMediaPiccola=calculateMediaMobile($api,$SymbolFeatures["symbol"],'15m');
    $arrayMediaMedia=calculateMediaMobile($api,$SymbolFeatures["symbol"],'30m');
    $arrayMediaGrande=calculateMediaMobile($api,$SymbolFeatures["symbol"],'1h');
    

}

        //mediamobile: https://www.php.net/manual/en/function.trader-ema.php
        //https://www.php.net/manual/en/book.trader.php
        //scalping
        // -STOP_LOSS
        // * -STOP_LOSS_LIMIT
        // * -TAKE_PROFIT

}



function calculatePrevPriceSell($prezzoPreso,$SymbolFeatures){
    global $percVenditaVincita;
    $prezzoVendita=$prezzoPreso + ($prezzoPreso*(float)$percVenditaVincita);

    $tickSize=(float)$SymbolFeatures['tickSize'];
    $prezzoVendita=(float)$prezzoVendita;
    $prezzoVendita=calculateTheRightMeasure($tickSize,$prezzoVendita); 
    return $prezzoVendita;
}






























function initTheGame($api){
    //Si inizia : 
    // Controllo se ho i soldi sul conto
    $checkOrderByMoneyGame=checkOrderByMoneyGame($api);
    //VERIFICO se è rimasto in sospeso un acquisto:
    $SymbolFeatures=leggiFileJson('simbolbuyed');
    if($SymbolFeatures){
        sellOcoProfitStop((array)$SymbolFeatures,$api);
    }else{
        if($checkOrderByMoneyGame){
        //PRENDO tutti i simbol con la moneta del giorno
        // e verifico la percentuale giornaliera se è buona
            $h24prevDay=getAllh24GoodSymbol($api);
            foreach($h24prevDay as $symbol){
                    handleInitWithOneSymbol($api,$symbol['symbol']);
            }
        }
    }
}




function getAllh24GoodSymbol($api){
    global $monetaDaGioco;
    global $MinPercentualeDiCrescita;
    $h24prevDay=$api->prevDay();
    //bnb AND var ++ in array
    //salvare array e poi?
    $arrayForGame=array();
    foreach($h24prevDay as $key => $value) {
        $criptoInSymbol=criptoInSymbol($monetaDaGioco,$value["symbol"]);
        if(
            $criptoInSymbol
        &
            $value["priceChangePercent"] >=$MinPercentualeDiCrescita
            // 0.50<$value["priceChangePercent"] 
            // &&
            // 1.90>$value["priceChangePercent"]
            )
            {
            $arrayForGame[]=$value;
            }
    }
    return $arrayForGame;
}




function handleInitWithOneSymbol($api,$symbol){
    // global $monetaDaGioco;
    $SymbolFeatures['symbol']=$symbol;
            //verifico la strategia di acquisto:
            $checkStrategy=checkStrategyBeforeToBuy($api,$SymbolFeatures);
            if($checkStrategy){
                 //carico il symbol di features:
                GetSymbolFeatures($SymbolFeatures,$api);
                // global $chatId;
                // sendMessage($chatId,"ORDER OCO ".$SymbolFeatures['symbol']. "price: ".$SymbolFeatures['LastPriceViewd']);            
                 return calculateOtherInfoAndBuy($api,$SymbolFeatures);

            }else{
                echo PHP_EOL.PHP_EOL."don't have right STRATEGY for ".$symbol.PHP_EOL.PHP_EOL;
            }
}


/**
 *  Verifica se si possono fare operazioni 
 * in base ai money nell 'account
 * in base agli ordini effettuati con quel symbol
 * 
 */
function checkOrderByMoneyGame($api){
        global $monetaDaGioco;
        $criptos=$api->account();
        $keyCripto=array_search($monetaDaGioco,array_column($criptos['balances'],'asset'));
        $quantity= (float)$criptos['balances'][$keyCripto]['free'];
        if ($quantity > 0) { return true;}
    return false;
}

/**
 * Verifica e sceglie la strategia da utilizzare 
 * per procedere all'acquisto
 * Candle: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
 */
function checkStrategyBeforeToBuy($api,&$SymbolFeatures){

        global $temporalView;

        $symbol=$SymbolFeatures['symbol'];

        if($symbol=='FARMBNB'){
            $symbofefe=true;
        }

        $SymbolFeatures['timeframe']='15m';
        // $candle1h=$api->getCandle($symbol,'1h');
        // $SymbolFeatures['upTrand1h']=$checkUpTrand=checkUpTrand($candle1h,3);
        $candle15m=$api->getCandle($symbol,$SymbolFeatures['timeframe']);

        $SymbolFeatures['checkNoDojiInArray']=$checkNoDojiInArray=checkNoDojiInArray($candle15m);
        $lastCandle=end($candle15m);
        $secondLastCandle=$candle15m[ count($candle15m) - 2  ];
        $checkGreenLastCandle=checkGreenCandle($lastCandle);
        $checkGreensecondLastCandle=checkGreenCandle($secondLastCandle);
        $checkLastCandleLongShodow=checkCandleLongShodow($lastCandle);
        $checksecondLastCandleLongShodow=checkCandleLongShodow($secondLastCandle);
        $arrayClose=array_column($candle15m,'close');

        //TRADE Strategy:

            //rsi e rsiSthoc
                // (sui 15 minuti per esempio sono consigliati i 20-5-5 o 17-9-3), invece per
                // quelli più alti si può lavorare con valori più bassi per essere più reattivi (5- 3-3 o 6-3-3).
                $rsi=trader_rsi($arrayClose,17);
                $trader_stochrsi=trader_stoch($rsi,$rsi,$rsi,17,9,TRADER_MA_TYPE_SMA,3,TRADER_MA_TYPE_SMA);// $trader_stochrsi=trader_stochrsi($arrayClose,17,9,3,TRADER_MA_TYPE_SMA);
                $SymbolFeatures['trader_stochrsiK']=$trader_stochrsiK= end($trader_stochrsi[0]);
                $SymbolFeatures['trader_stochrsiD']=$trader_stochrsiD= end($trader_stochrsi[1]);
                $SymbolFeatures['rsi']=end($rsi);
                $rsiUp70=$SymbolFeatures['rsi']>70;
                $stoChKUpD=$trader_stochrsiK>$trader_stochrsiD;
                // $trader_stochrsiD < 20 almeno una volta e adesso maggiore di 20 e minore di 78.
                // il punto in cui è minore, è prima <- del MACD salga.




            //EMA && SMA && MACD:
                $trader_macd=trader_macd($arrayClose,12,26,9);
                    $signalLine= $trader_macd[1];
                    $Macd= $trader_macd[0];
                    
                    $SymbolFeatures['MACD']=end($Macd);
                    $SymbolFeatures['signalLine']=end($signalLine);
                // $exponentialMovingAverage12=exponentialMovingAverage($arrayClose,12);
                // $emaMovingAverage12=trader_ema($arrayClose,12);
                // $SymbolFeatures['emaMovingAverage12']=end($emaMovingAverage12);
                // $emaMovingAverage26=trader_ema($arrayClose,26);
                // $SymbolFeatures['emaMovingAverage26']=end($emaMovingAverage26);
                // $signalLine=$emaMovingAverage9=trader_ema($arrayClose,9);
                // $SymbolFeatures['signalLine']=end($signalLine);
                // $differentialLine=differentialLineForMacd($emaMovingAverage12,$emaMovingAverage26);
                // $SymbolFeatures['differentialLine']=end($differentialLine);
                // $MACD=$SymbolFeatures['trandUpFromMACD']=$SymbolFeatures['differentialLine']>$SymbolFeatures['signalLine'];
                //strategy with trandUp or TrandDown
                    $emaMovingAverage21=trader_ema($arrayClose,21);
                    
                    $emaMovingAverage100=trader_ema($arrayClose,100);
                    
                    $smaMovingAverage200=trader_sma($arrayClose,200);
                    

                    $SymbolFeatures['checkCrossUp21To100']=
                    $checkCrossUp21To100=checkFastOnCrossSlow($emaMovingAverage21,$emaMovingAverage100,$temporalView);
                    $SymbolFeatures['checkCrossUp21ToClose']=
                    $checkCrossUp21ToClose=checkFastOnCrossSlow($arrayClose,$emaMovingAverage21,$temporalView);
                    

                    $SymbolFeatures['emaMovingAverage21']=end($emaMovingAverage21);
                    $SymbolFeatures['emaMovingAverage100']=end($emaMovingAverage100);
                    $SymbolFeatures['smaMovingAverage200']=end($smaMovingAverage200);

                    $temporalViewLast=array_slice($emaMovingAverage21, -$temporalView, 1)[0];
                    $lastema21=end($emaMovingAverage21);
                    $straightSlopeLinePositive21=($temporalViewLast)<($lastema21);
                    $SymbolFeatures['straightSlopeLine21']=$straightSlopeLinePositive21;

                    $check21UpTo100ema=$SymbolFeatures['emaMovingAverage21']>$SymbolFeatures['emaMovingAverage100'];
                    $check21UpTo200sma=$SymbolFeatures['emaMovingAverage21']>$SymbolFeatures['smaMovingAverage200'];
                    

                    $SymbolFeatures['checkMACDnear']=$checkMACDnear=checkFastOnCrossSlow($Macd,$signalLine,$temporalView);
                    //la linea del MACD supera, incrocia verso l'alto la signalline -> acquisto.
                    $MACDUpZero=$SymbolFeatures['MACD']>0;

                   
                    


                    // //COUNT: 
                    // $SymbolFeatures['count']['signalLine']=count($signalLine);
                    // $SymbolFeatures['count']['differentialLine']=count($differentialLine);
                    // $SymbolFeatures['count']['emaMovingAverage21']=count($emaMovingAverage21);
                    // $SymbolFeatures['count']['emaMovingAverage100']=count($emaMovingAverage100);
                    // $SymbolFeatures['count']['smaMovingAverage200']=count($smaMovingAverage200);
                    // $SymbolFeatures['count']['arrayClose']=count($arrayClose);

            //Strategia di prova: end di 21 sopra end di 100
            // e MACD appena crossOver.
            // aggiungere stoChKeD sopra 20 ipervenduto?
            $strategyTry=$check21UpTo100ema && $check21UpTo200sma && $checkMACDnear && $rsiUp70 && $stoChKUpD;

            //stretegia Tesi: 
            $strtegyTesi=$checkCrossUp21To100 && $check21UpTo200sma  && $checkCrossUp21ToClose  && $checkMACDnear && $MACDUpZero;
            $SymbolFeatures['strategy']['strategyTry']=$strategyTry;
            $SymbolFeatures['strategy']['strtegyTesi']=$strtegyTesi;
        if(
            

            // $checkCrossUp21To100  && $checkCrossUp21ToClose   // trend rialzista
            // &&
            $strategyTry  || $strtegyTesi
            // && $checkMACDnear
            // &&
            // $checkUpTrand     // trand rialzista
            // &&
            // $checkNoDojiInArray  // no candele doji
            // &&
            // $checkGreenLastCandle //l'ultima candela dei 5m deve essere verde
            // &&
            // !$checkGreensecondLastCandle // la penultima candela  dei 5m deve essere rossa
            // &&
            // !$checkLastCandleLongShodow   // l'ultima candela non deve avere una lunga ombra 
            // &&
            // !$checksecondLastCandleLongShodow //la penultima candela non deve avere una lunga ombra 
        ){
            return true;
        }
        return false;
}


/**
 * Calcola AND Buy:
 * la quantità
 * price
 */
function calculateOtherInfoAndBuy($api,$SymbolFeatures){
    $SymbolFeatures=CalculateQuantity($SymbolFeatures,$api);
    if(!$SymbolFeatures){return false;}
    //QUANTITY for 2.
    // $SymbolFeatures['quantityToBuy']=$SymbolFeatures['quantityToBuy']*2;
    global $test;
    if(!$test){
        $order = $api->buy($SymbolFeatures["symbol"], formatNumber($SymbolFeatures['quantityToBuy']), "","MARKET");
    }else{
        $order['status']='FILLED';
    }
    if(isset($order['status']) && $order['status']=='FILLED'){
        $SymbolFeatures['executedQty']=(float)$order['executedQty'];
        $SymbolFeatures['OrderBuy']=$order['fills'];
        writeInJson('simbolbuyed',$SymbolFeatures);
        global $chatId;
        sendMessage($chatId,"Comprato: ".$SymbolFeatures['symbol']. "price: ".$SymbolFeatures['LastPriceViewd']);
        return sellOcoProfitStop($SymbolFeatures,$api);
    }
    return false;
}




/** *
 * price
 * stopprice 
 * stoplimitprice
 * 
 * Lastprice>>stopprice>stoplimitprice */

function CalculatePriceAndQtyToSellOCO(&$SymbolFeatures,$api){
    $SymbolFeatures=(array)$SymbolFeatures;

    //Prezzo preso:
    $SymbolFeatures['lastOrder']=getLastOrderBySymbol($api,$SymbolFeatures['symbol']);
    $prezzoPreso=(float)$SymbolFeatures['lastOrder']['price'];
    global $test;
    if($test){$prezzoPreso=(float)$SymbolFeatures['LastPriceViewd'];}


    $tickSize=(float)$SymbolFeatures['tickSize'];
   

    //stopprice:
    global $percVenditaPerdita;
    $prezzoVenditaPerdita=$prezzoPreso - ($prezzoPreso*(float)$percVenditaPerdita);
    $stopprice=$prezzoVenditaPerdita=calculateTheRightMeasure($tickSize,$prezzoVenditaPerdita); 
    $SymbolFeatures['stopprice']=$stopprice;
    //RAPPORTO 3:1
    $prezzoVendita=3*($prezzoPreso-$prezzoVenditaPerdita)  +  $prezzoPreso;

    //PREZZO di Vendita(TakeProfit):
    $prezzoVendita=calculateTheRightMeasure($tickSize,$prezzoVendita); 
    $SymbolFeatures['prezzoVendita']=$prezzoVendita;

    //stoplimitprice:
    $stoplimitprice=$stopprice-($tickSize*10);
    $SymbolFeatures['stoplimitprice']=$stoplimitprice;
   
    //QUANTITY:
    $quantityforsell=getQuantityFromAccount($SymbolFeatures['symbol'],$api);
    $SymbolFeatures["quantityForSellNONRound"]=$quantityforsell;
    $stepSize=(float)$SymbolFeatures['stepSize'];
    $SymbolFeatures["quantityForSell"]=calculateTheRightMeasure($stepSize,$quantityforsell,true);
    return $SymbolFeatures;
}


/**
 * 
 * Vendita in takeprofit 
 * 
 */
function sellOcoProfitStop($SymbolFeatures,$api){
    CalculatePriceAndQtyToSellOCO($SymbolFeatures,$api);
    writeInJson('simbolbuyed',$SymbolFeatures);
    global $test;
    if(!$test){
        $order = $api->ocoOrder( 'SELL', $SymbolFeatures['symbol'], formatNumber($SymbolFeatures['quantityForSell']), formatNumber($SymbolFeatures['prezzoVendita']), formatNumber($SymbolFeatures['stopprice']),formatNumber($SymbolFeatures['stoplimitprice']));
    }else{
        $order=array();
        $order['orderReports']=true;
    }
    $SymbolFeatures['orderOCO']=$order;
    if(isset($order['orderReports'])){
        writeInJson('simbolbuyed','');
        $arraySchedule=leggiFileJson('simbolScheduled');
        $arraySchedule[]=$SymbolFeatures;
        writeInJson('simbolScheduled',$arraySchedule);
        writeInJson('updatingPrice','');
        global $chatId;
        sendMessage($chatId,"ORDER OCO ".$SymbolFeatures['symbol']." prezzo vendita TAKE profit: ".$SymbolFeatures['prezzoVendita']." prezzo vendita STOP LOSS profit:".$SymbolFeatures['stopprice']);
        echo PHP_EOL."ORDER OCO ".$SymbolFeatures['symbol']." prezzo vendita TAKE profit: ".$SymbolFeatures['prezzoVendita']." prezzo vendita STOP LOSS profit:".$SymbolFeatures['stopprice'].PHP_EOL.PHP_EOL ;
        return true; 
    }
    return false;
}














































?>