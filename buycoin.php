<?php
require_once 'handlerBuySell.php';
$api = newBinance("api");
initConfiguration();
initTheGame($api);
 ?>