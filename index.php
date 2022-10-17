<?php

require './lib/autoloader.php';

$cls = new AutoCreateScript();
$dbo = new Dbo();
$all = $dbo->getAll("select * from users where isValid=1 order by date_create");

var_dump($dbo->getError());