<?php

/**
 *    File: conf.php
 *    Name: lacksfish
 *    Date: 12/15/13
 * Project: Primecoinchains
 *    Desc: This script the backbone of all MySQL related functions
 */

error_reporting(E_ALL);

#Define (point to) primecoind dir and primecoind datadir
$primecoindPath = "/bin/primecoind";
$primecoindDataDir = "/home/lacksfish/.primecoin";

#Primechainer.exe is the communicator bridge between the PHP scripts and the primecoin daemon
if(!defined('PRIMECHAINERPATH')) define('PRIMECHAINERPATH', '/PATHTOPRIMECHAINER/primechainer.exe');

#Define MySQL information
if(!defined('MYSQL_HOST')) define('MYSQL_HOST', 'localhost');

if(!defined('MYSQL_DB')) define('MYSQL_DB', 'DATABASENAME');
if(!defined('MYSQL_USER')) define('MYSQL_USER', 'USERNAME');
if(!defined('MYSQL_PW')) define('MYSQL_PW', 'PASSWORD');

?>
