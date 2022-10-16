<?php

include './lib/config/dbo.php';

$dbo = new Dbo();
$dbo->con();

var_dump($dbo->getError());
