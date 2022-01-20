<?php

function writeInJson($nomeJson,$jsonArray){
    global $path;
    $nomeJson=$nomeJson.'.json';
    $jsonArray=json_encode($jsonArray,true);
    if (file_put_contents($path.$nomeJson, $jsonArray))
        return true;
    else 
        return false;
}

function leggiFileJson($File){
    global $path;
    if(file_exists($path.$File.".json")){
    $Jsonfile=file_get_contents($path.$File.".json",true);
    return json_decode($Jsonfile);
    }
    return false;
}




function transformArrayToObject($array)
{
    $object = new stdClass();
    foreach ($array as $key => $value) {
        $object->$key = $value;
    }
    return $object;
}


function searchBysymbol($symbol, $array) {
    foreach ($array as $key => $val) {
        if ($val->symbol === $symbol) {
            return $key;
        }
    }
    return null;
 } 


 function criptoInSymbol($cripto,$symbol){
    $pos=-1;
        $subSymbol= substr($symbol,-4);
        $pos = strpos($subSymbol, $cripto);
        if(!($pos===false)){
           return true;
    }
    return false;
 }

 function getOtherCriptoInSymbol($cripto,$symbol){
    $pos=-1;
        $subSymbol= substr($symbol,-4);
        $pos = strpos($subSymbol, $cripto);
        if(!($pos===false)){
            return str_replace($cripto,"",$symbol);
    }
    return false;
 }

 function YetBuyedSymbol(){
    return leggiFileJson('simbolbuyed');
}

//16:40-16:50
function diffFrom2DateinMinute($dateOne,$dateTwo){
    $data1 = new DateTime($dateOne);
    $data2 = new DateTime($dateTwo);
    $interval = $data1->diff($data2);
    $minutes = $interval->d * 24 * 60;
    $minutes += $interval->h * 60;
    $minutes += $interval->i;
    return $minutes;
}

function handleDate($time){
    $dt = new DateTime();
    $dt->setTimestamp($time / 1000);
    return  $dt->format('Y-m-d H:i:s.u');
}

function roundDown($decimal, $precision)
{
    $sign = $decimal > 0 ? 1 : -1;
    $base = pow(10, $precision);
    return floor(abs($decimal) * $base) / $base * $sign;
}

function GetLastPriceBySymbol($symbol,$api){
    $prices=$api->prices();
    foreach($prices as $key => $value) {
        if($symbol===$key){
            return $value;
        }
    }
    return false;
}

function GetLastPriceByCripto($cripto,$api){
    $prices=$api->prices();
    foreach($prices as $key => $value) {
        if(criptoInSymbol($cripto,$key)){
            return $value;
        }
    }
    return false;
}

function sortFunction( $b, $a ) {
  return new DateTime($a['DateTime']) <=> new DateTime($b['DateTime']);
}

function getLastOrderBySymbol($api,$symbol){
      $result = $api->history($symbol);
      foreach($result as $key => $value) {
        $result[$key]['date']=date("d-m-Y H:i:s", ( $result[$key]['time'] / 1000) );
        $dt = new DateTime();
        $dt->setTimestamp($result[$key]['time'] / 1000);
        $result[$key]['DateTime']=$dt->format('d-m-Y H:i:s.u');
      }
      usort($result, "sortFunction");
    return $result[0];
}


 function GetSymbolFeatures($symbol,$api){
    $valuefilters=array(); 
    $info = $api->exchangeInfo();
    $fee=$api->tradeFee($symbol);
    $SymbolFeatures['serverTime']=handleDate($info['serverTime']);
    $SymbolFeatures['timezone']=$info['timezone'];
    $valuefilters=array(); 
    foreach($info['symbols'] as $key => $value) {
        if($symbol===$value['symbol']){
            $valuefilters=$value['filters'];
            $SymbolFeatures['symbol']=$symbol;
        }
    }
    foreach($valuefilters as $keyfilter => $valuefilter) {
        if($valuefilter["filterType"] ==="MIN_NOTIONAL" ){
            $SymbolFeatures['minNotional']=(float)$valuefilter["minNotional"];
        }
        if($valuefilter["filterType"] ==="LOT_SIZE" ){
            $SymbolFeatures['stepSize']=(float)$valuefilter["stepSize"];
        }
        if($valuefilter["filterType"] ==="PRICE_FILTER" ){
            $SymbolFeatures['tickSize']=(float)$valuefilter["tickSize"];
        }
    }
    // $SymbolFeatures['baseFee']=(float)$fee['tradeFee'][0]['maker'];
    $SymbolFeatures['baseFee']=(float)$fee[0]['makerCommission'];
    // $SymbolFeatures['quoteFee']=(float)$fee['tradeFee'][0]['taker'];
    $SymbolFeatures['quoteFee']=(float)$fee[0]['takerCommission'];
    $SymbolFeatures['LastPriceViewd']=GetLastPriceBySymbol($symbol,$api);
    return $SymbolFeatures;
}


function MaxVarPercSymbolFrom2Array($datarrayPrima,$dataarrayDopo){
    $max=array();
    global $monetaDaGioco;
    $max['VarPerc']=0;
    $arrayVarPerc=array();
    $datarrayPrima=(array)$datarrayPrima;
    $dataarrayDopo=(array)$dataarrayDopo;
    $datarrayPrima['ServerTime']=0;
    $dataarrayDopo['ServerTime']=0;
        foreach($datarrayPrima as $key => $value) {
            if((float)$dataarrayDopo[$key]>(float)$value && criptoInSymbol($monetaDaGioco,$key)){
                $temporyVar=VariazionePerc((float)$dataarrayDopo[$key],(float)$value);
                $arrayVarPerc[$key]['varPerc']=$temporyVar;
                if($temporyVar>$max['VarPerc']){
                    $max['VarPerc']=(float)$temporyVar;
                    $max['symbol']=$key;
                    $max['prezzoFinale']=(float)$dataarrayDopo[$key];
                }
            }
        }
    return $max;
}

function calculateTheRightMeasure($step,$initialQuantity,$under=false){
    $precision=strlen(rtrim(substr($step, strpos($step, '.', 1) + 1), '0'));
    if(strripos($step,"E")){
        $precision=substr(trim($step), -1);
    }
    $quantityToarrive=$initialQuantity;
    $initialQuantity=roundDown($quantityToarrive, $precision);
    $result=$initialQuantity;
    while($initialQuantity<$quantityToarrive){
        $initialQuantity=$initialQuantity+$step;
        if($initialQuantity>$quantityToarrive && $under){
            return $result;
        }else{
            $result=$initialQuantity;
        }
    }
    return $result;
}

function sendMessage($id,$text){
    global $token;
    $url= $token."/sendMessage?chat_id=$id&text=".urlencode($text);
    file_get_contents($url);
}


