<?php
 
$api = "http://37.60.231.179/~beadmast/beads/api/soap/?wsdl=1";
 
$username = 'anas';
$password = '30104987212';
 
$cli = new SoapClient($api);
 $infoHash = $cli->cacheTorrent(base64_encode(file_get_contents("my.torrent")));
//retreive session id from login
$session_id = $cli->login($username, $password);
 
//call customer.list method
$result = $cli->call($session_id, 'customer.list', array(array()));
var_dump($infoHash);