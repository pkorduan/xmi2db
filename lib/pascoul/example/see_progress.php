<?php
require_once('../pascoul.php');
$pascoul = new Pascoul();
$pascoul->client_reconnect = 1; //the time for the client to reconnect after the connection has lost in seconds. Default: 1.
$pascoul->allow_cors = true; //Allow cross-domain access? Default: false. If you want others to access this must set to true.
$pascoul->start();

if (isset($_REQUEST['truncate'])) $pascoul->send_message(0, $_REQUEST['truncate'], 5);
if (isset($_REQUEST['schema'])) $pascoul->send_message(0, $_REQUEST['schema'], 5);
  
//LONG RUNNING TASK
for($i = 1; $i <= 10; $i++) {
    $pascoul->send_message($i, 'on iteration ' . $i . ' of 10' , $i*10); 
 
    sleep(1);
}
 
$pascoul->send_message('CLOSE', 'Process complete');
?>