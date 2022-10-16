<?php

include './lib/config/dbo.php';

$con = new Dbo();
 
try {
    $pst = $con->getDbo()->prepare("select * from users");
    $pst->execute();
} catch (Exception $e) {
    var_dump($e);
}
