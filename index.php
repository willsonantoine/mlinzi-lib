<?php

require './lib/autoloader.php';

$cls = new AutoCreateScript();
$cls->Start();
var_dump($cls->getError());