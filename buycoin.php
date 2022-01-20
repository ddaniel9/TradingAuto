<?php
require_once 'handlerBuySell.php';
$api = newBinance("api");
initConfiguration();
handleInitWithOneSymbol($api);
 ?>