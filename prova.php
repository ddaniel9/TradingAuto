<?php
//set XDEBUG_SESSION=1
//set XDEBUG_SESSION=0
require_once 'handlerBuySell.php';
       //AUTENTICAZIONE
       $api = newBinance("api");
//    $result=array();
        // $result=$api->prices();
        // $result=$api->prices();
        
        // $datarrayPrima=(array)leggiFileJson("updatingPrice");
        // $result=  MaxVarPercSymbolFrom2Array($datarrayPrima,$api->prices());
// date_default_timezone_set('America/New_York');
// $result=getQuantityPurchasedBySymbol($api,"HOTEUR");
// $time=$result[0]['time'];
// $arraytime=array();
// $arraytime[]=$time;
//  $result=leggiUpdatePriceControll($api);

// roundStep($qty, $stepSize = 0.1)
// roundTicks($price, $tickSize)
//getCandle(string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null)
// $arrayMedia5=calculateMediaMobile($api,'TRXBNB','5m');
// $order = $api->buy('TRXBNB', 321, "","MARKET");
                        // $result=$api->getCandle('BNBBTC','1h');
                        // $result2=checkUpTrand( $result,  3);
                        // $arrayClose=array_column($result,'close');
                        // echo json_encode("arrayClose").PHP_EOL;
                        // echo json_encode($arrayClose);
                        // echo PHP_EOL.json_encode("trader_rsi");
                        // echo json_encode(trader_rsi((array)$arrayClose,5));
                        // echo PHP_EOL.json_encode("trader_stochrsi");
                        // echo json_encode(trader_stochrsi($arrayClose,14,3,3,TRADER_MA_TYPE_SMA));
                        // echo PHP_EOL.json_encode("exponentialMovingAverage");
                        // echo json_encode(exponentialMovingAverage((array)$arrayClose,5));

        initConfiguration();
        $candle5m=$api->getCandle('AVABNB','15m');// come da grafico
        $arrayClose=array_column($candle5m,'close');// come da grafico
        echo json_encode("arrayClose: ").PHP_EOL;
        echo json_encode($arrayClose).PHP_EOL;
                        // $SymbolFeatures['trader_rsi']=array_reverse(trader_rsi($SymbolFeatures['arrayClose'],5));
        // $trader_stochrsi=array_reverse(trader_stochrsi($arrayClose,17,9,3,TRADER_MA_TYPE_SMA));
                // $trader_stochrsi=(trader_stochrsi($arrayClose,17,9,3,TRADER_MA_TYPE_SMA));
                // echo json_encode("trader_stochrsi: ").PHP_EOL;
                // echo json_encode($trader_stochrsi).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;
                // // $exponentialMovingAverage12=exponentialMovingAverage($arrayClose,12);// come da grafico
                $exponentialMovingAverage12=trader_sma($arrayClose,12);
                echo json_encode("SMA 12 : ").PHP_EOL;// come da grafico
                echo json_encode($exponentialMovingAverage12).PHP_EOL;
                $exponentialMovingAverage26=trader_sma($arrayClose,26);
                echo json_encode("SMA 26 : ").PHP_EOL;// come da grafico
                echo json_encode($exponentialMovingAverage26).PHP_EOL;
                // $signalLine=$exponentialMovingAverage9=trader_sma($arrayClose,9);
                // echo json_encode("SMA 9 : ").PHP_EOL;// come da grafico
                // echo json_encode($exponentialMovingAverage9).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

                // $rsi=trader_rsi($arrayClose,17);
                // echo json_encode("trader_rsi : ").PHP_EOL;
                // echo json_encode($rsi).PHP_EOL;

                // $stochrsi = trader_stoch($rsi,$rsi,$rsi,17,9,TRADER_MA_TYPE_SMA,3,TRADER_MA_TYPE_SMA);
                // echo json_encode("trader_stoch : ").PHP_EOL;
                // echo json_encode($stochrsi).PHP_EOL; // come da grafico

                $exponentialMovingAverage100=trader_ema($arrayClose,100);
                echo json_encode("ema 100 : ").PHP_EOL;// come da grafico
                echo json_encode($exponentialMovingAverage100).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

                  $exponentialMovingAverage21=trader_ema($arrayClose,21);
                echo json_encode("ema 21 : ").PHP_EOL;// come da grafico
                echo json_encode($exponentialMovingAverage21).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

                $signalLine=$exponentialMovingAverage9=trader_sma($arrayClose,9);
                echo json_encode("sma 9 : ").PHP_EOL;// come da grafico
                echo json_encode($exponentialMovingAverage21).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

                $differentialLine=differentialLineForMacd($exponentialMovingAverage26,$exponentialMovingAverage12);
                echo json_encode("differentialLine : ").PHP_EOL;// come da grafico
                echo json_encode($differentialLine).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;
                checkFastOnCrossSlow($signalLine,$differentialLine,60);



                 // DA TESTARE:
                //  trader_stochf(
                //         array $high,
                //         array $low,
                //         array $close,
                //         int $fastK_Period = ?,
                //         int $fastD_Period = ?,
                //         int $fastD_MAType = ?
                //     ): array


                 $traderMom=trader_mom($arrayClose, 21);
                 echo json_encode("traderMom : ").PHP_EOL;// come da grafico
                echo json_encode($traderMom).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;


                $traderCmo=trader_cmo($arrayClose, 21);
                 echo json_encode("traderCmo : ").PHP_EOL;// come da grafico
                echo json_encode($traderCmo).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;



                $traderTrima=trader_trima($arrayClose, 21);
                 echo json_encode("traderTrima : ").PHP_EOL;// come da grafico
                echo json_encode($traderTrima).PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

        // (sui 15 minuti per esempio sono consigliati i 20-5-5 o 17-9-3), invece per
        // quelli più alti si può lavorare con valori più bassi per essere più reattivi (5- 3-3 o 6-3-3).
        // $differentialLine=differentialLineForMacd($exponentialMovingAverage26,$exponentialMovingAverage12);
        //   if   $signalLine>$differentialLine; => Rilazista
// $result2=checkOrderByMoneyGame($api);



// $real = array(12,15,17,19,21,25,28,12,15,16);
// $timePeriod = 1;
// $data = trader_ema($real,$timePeriod);
// echo json_encode($data);

// $order = $api->sell('TRXBNB', 321, "0.0001569","STOP_LOSS_LIMIT",$flags);//funziona
   // $order = $api->sell('TRXBNB', 321, "0.0001569","STOP_LOSS_LIMIT",$flags);
// $order = $api->sell('TRXBNB', 321, "0.0001577","TAKE_PROFIT_LIMIT",$flags);
                        // // OCO:
                        // // 	SELL:
                        // // 		take profit: price
                        // // 		stop : 0,021
                        // // 		limit : 0,020(per essere sicuro che fa ordine al mercato).
                        // $order = $api->ocoOrder( 'SELL', 'BNBBTC', 0.025, 0.011080, 0.011012, 0.011010);
                        //                ocoOrder('SELL', 'AXSBNB', 0.33, 0.1619, 0.1611, 0.1609)
                        //                ocoOrder('SELL', 'AXSBNB', 0.33, 0.1618, 0.161, 0.1608)
//handleDate($time)
//                                    $arraytime[]=date("d-m-Y H:i:s", ($time / 1000) ); 
//$fee['tradeFee'][0]['maker'];
// $keyCripto=array_search('WIN',array_column($result['balances'],'asset'));
// echo json_encode($result[ count($result) - 1  ]).PHP_EOL;

// echo json_encode($arrayClose);

 ?>