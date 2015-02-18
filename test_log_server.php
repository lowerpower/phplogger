#!/usr/bin/php
<?php
//
// test the UDPLogRX Class, run after any changes
//

define('LOGROOTDIR', "/home/mike/unix/logger/log/");
//define('LOGGERVERBOSE', 1);

require_once('log_receiver.php');

echo("create new log reciver\n");
$udprx=new UDPLogRX();
echo("change log base directory\n");
$udprx->set_log_root_dir('LOGROOTDIR');
/*
echo("Adde events for test\n");
$udprx->log_insert("test1","log1");
$udprx->log_insert("test2","log1");
$udprx->log_insert("test1","log2");
$udprx->log_insert("test2","log1");
$udprx->log_insert("test1","log3");
$udprx->flush();
$udprx->flush();

$udprx->log_insert("test3","log this 3");
$udprx->log_insert("test3","log th 23");
$udprx->log_insert("test3","log --3");
$udprx->log_insert("test1","final");

$udprx->flush();
$udprx->flush();
*/
echo("go into log forever mode\n");

$udprx->log_forever();


