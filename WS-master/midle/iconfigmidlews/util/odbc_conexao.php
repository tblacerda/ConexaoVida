<?php

#conecta no innovative
//$connect = @odbc_connect('integracaoInnovative','sa','');
$user = 'sa';
$pass = '';
$server = 'INNOVATIVE';
$database = 'InnovativeSuite2';

// No changes needed from now on
$connection_string = "DRIVER={SQL Server};SERVER=$server;DATABASE=$database;"; 
$connect = odbc_connect($connection_string,$user,$pass);
