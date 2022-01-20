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

echo json_encode($result2);

 ?>