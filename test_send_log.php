#!/usr/bin/php
<?php
//
// test the UDPLogger Class, run after any changes
//

//define('LOGGERVERBOSE', 1);

require_once('logger.php');

echo("create new log reciver\n");
$logme= new UDPLogger("localhost",1025);

$logme->logit("newtest","this is a log");
$logme->logit("newtest","this is a log1");
$logme->logit("newtest","this is a log2");
$logme->logit("newtest","this is a log3");


