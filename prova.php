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
$arrayMedia5=calculateMediaMobile($api,'TRXBNB','5m');
// $order = $api->buy('TRXBNB', 321, "","MARKET");
$flags=array();
$flags['stopPrice']='0.0001569';
// $order = $api->sell('TRXBNB', 321, "0.0001569","STOP_LOSS_LIMIT",$flags);//funziona
$order = $api->sell('TRXBNB', 321, "0.0001569","STOP_LOSS_LIMIT",$flags);
// $order = $api->sell('TRXBNB', 321, "0.0001577","TAKE_PROFIT_LIMIT",$flags);
//handleDate($time)
//                                    $arraytime[]=date("d-m-Y H:i:s", ($time / 1000) ); 
//$fee['tradeFee'][0]['maker'];
// $keyCripto=array_search('WIN',array_column($result['balances'],'asset'));
// echo json_encode($result[ count($result) - 1  ]).PHP_EOL;
echo json_encode($order);

 ?>