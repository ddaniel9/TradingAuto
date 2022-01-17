<?php

function VariazionePerc($finale,$iniziale){
    return (($finale - $iniziale)/$iniziale) * 100;
}

function VariazioneDiDifferenza($lpw,$pf){
    return  (($lpw-$pf)*100)/$lpw;
}

//prima di chimare questa funzione chiamare getFeauters();
function CalculateQuantity($SymbolFeatures,$api){
    global $quantityMoltiplicator;
    $stepSize=$SymbolFeatures['stepSize'];
    $commissionToBuy=(float)$SymbolFeatures['baseFee'];
    $commissionToSell=(float)$SymbolFeatures['quoteFee'];
    $lastPrice=GetLastPriceBySymbol($SymbolFeatures['symbol'],$api);
    $MinNotion=(float)$SymbolFeatures['minNotional'];
    
    $quantity=$MinNotion/(float)$lastPrice;

    

    $quantity= ((float)$quantity*(float)$quantityMoltiplicator);

    //per 3 perché viene tolta alla vendita 
    // con questa modifica non dovrebbe entrare più nella funzione
    //handlerErrorquantityFee e aumentare il contatore di errore
    // $quantity=((float)$quantity) + 3*((float)$quantity*(float)$commissionToBuy);


    // si aggiungere baseFeeRound alla quantità, per prevenire 
    //che binance non riesce a comprare tutta la quantità e compra di meno.
    // $commissionRoundToBuy=calculateTheRightMeasure((float)$stepSize,(float)$quantity*(float)$commissionToBuy);
    // $quantity=((float)$quantity) + $commissionRoundToBuy;
    // $SymbolFeatures['baseFeeRound']=$commissionRoundToBuy;

    $quantity=((float)$quantity) + ((float)$quantity*(float)$commissionToBuy);


    $quantityQuote=$quantity*(float)$lastPrice;
    $quantity=((float)$quantity) + (((float)$quantityQuote*(float)$commissionToSell)/$lastPrice);

    $quantity=$quantity+$stepSize;

    $quantity=calculateTheRightMeasure((float)$stepSize,$quantity);

    $SymbolFeatures['quantityToBuy']=$quantity;
    return $SymbolFeatures;
    }


function CalculatePriceAndQtyToSell($SymbolFeatures,$api){
    $SymbolFeatures['lastOrder']=getLastOrderBySymbol($api,$SymbolFeatures['symbol']);
    $prezzoPreso=(float)$SymbolFeatures['lastOrder']['price'];
    // $prezzoPreso=(float)$SymbolFeatures['LastPriceViewd'];
    $commission=(float)$SymbolFeatures['lastOrder']['commission'];
    $MinNotion=(float)$SymbolFeatures['minNotional'];
    $SymbolFeatures=(array)$SymbolFeatures;
    global $percVenditaVincita;
    $prezzoVendita=$prezzoPreso + ($prezzoPreso*(float)$percVenditaVincita);

    $tickSize=(float)$SymbolFeatures['tickSize'];
    $prezzoVendita=(float)$prezzoVendita;
    $prezzoVendita=calculateTheRightMeasure($tickSize,$prezzoVendita); 

    // $quantityforsell=(float)$SymbolFeatures['executedQty'];
    // $stepSize=(float)$SymbolFeatures['stepSize'];
    // // $quantityforsell=$quantityforsell-$commission;
    // $quantityforsell=calculateTheRightMeasure($stepSize,$quantityforsell);   
    
    // $error=handlerErrorquantityFee($SymbolFeatures);
    // if($error){
    //     $commission=calculateTheRightMeasure((float)$stepSize,$commission);
    //     $quantityforsell=$quantityforsell-$commission;
    //     while(($prezzoVendita*$quantityforsell)<$MinNotion){
    //         $prezzoVendita=$prezzoVendita+$tickSize;
    //     }
    // }
    
    $quantityforsell=getQuantityFromAccount($SymbolFeatures,$api);
    $SymbolFeatures["quantityForSellNONRound"]=$quantityforsell;
    $stepSize=(float)$SymbolFeatures['stepSize'];
    $SymbolFeatures["quantityForSell"]=calculateTheRightMeasure($stepSize,$quantityforsell,true);
    $SymbolFeatures['prezzoVendita']=$prezzoVendita;
    return $SymbolFeatures;
  }


function getQuantityFromAccount($SymbolFeatures,$api){
    global $monetaDaGioco;
    $cripto=getOtherCriptoInSymbol($monetaDaGioco,$SymbolFeatures['symbol']);
    $criptos=$api->account();
    $keyCripto=array_search($cripto,array_column($criptos['balances'],'asset'));
    $quantity= (float)$criptos['balances'][$keyCripto]['free'];
    return $quantity;
}

function handlerErrorquantityFee(&$SymbolFeatures){
    if(isset($SymbolFeatures['countError'])){
        $SymbolFeatures['countError']=(float)$SymbolFeatures['countError']+1;
    }else{
        $SymbolFeatures['countError']=1;
    }
    if($SymbolFeatures['countError']>1){
        return true;
    }else{
        return false;
    }

}


function getLastCandle($api,$symbol,$interval='5m'){
    $result=$api->getCandle($symbol,$interval);
    $secondLast=$result[ count($result) - 2  ];
    $last=end($result);
    $last['prev']=$secondLast;
    $last['priceChange']=($last['open']-$last['prev']['close']);
    $last['priceChangePercent']=($last['priceChange']/$last['prev']['close'])*100;
    $last['volumeChange']=($last['volume']-$last['prev']['volume']);
    if($last['prev']['volume']!=0){
    $last['volumeChangePercent']=($last['volumeChange']/$last['prev']['volume'])*100;
}else{
    $last['volumeChangePercent']=($last['volumeChange']);
}
    return $last;
}



function calculateMediaMobile($api,$symbol,$interval='5m'){
    $result=$api->getCandle($symbol,$interval);
    $arrayClose=array_column($result,'close');
    $arrayMedia=exponentialMovingAverage($arrayClose,5);
    return $arrayMedia;
}


function exponentialMovingAverage(array $numbers, int $n): array
{
    $numbers=array_reverse($numbers);
    $m   = count($numbers);
    $α   = 2 / ($n + 1);
    $EMA = [];

    // Start off by seeding with the first data point
    $EMA[] = $numbers[0];

    // Each day after: EMAtoday = α⋅xtoday + (1-α)EMAyesterday
    for ($i = 1; $i < $m; $i++) {
        $EMA[] = ($α * $numbers[$i]) + ((1 - $α) * $EMA[$i - 1]);
    }
    $EMA=array_reverse($EMA);
    return $EMA;
}




