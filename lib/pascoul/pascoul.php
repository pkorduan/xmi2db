<?php
// +----------------------------------------------------------------------+
// | PHP Asynchronous Loader - Pascoul                                    |
// | - PHP class -                                                        |
// | Long-running processes with user feedback                            |
// | Requirements: PHP5, HTML 5 compatible browser                        |
// +----------------------------------------------------------------------+
// | Author: Christian Seip <christian.seip@gdi-service.de>               |
// +----------------------------------------------------------------------+
// based on
//  http://www.htmlgoodies.com/beyond/php/show-progress-report-for-long-running-php-scripts.html
//  https://github.com/licson0729/libSSE-php

class Pascoul {
  //Allow Cross-Origin Access?
  //Default: false
  public $allow_cors = false;

  //the time client to reconnect after connection has lost in seconds
  //default: 1
  public $client_reconnect = 1;

  public static function start() {
    if( php_sapi_name() == 'cli' )
      return;

    //send the proper header
    header('Content-Type: text/event-stream');
    // recommended to prevent caching of event data.
    header('Cache-Control: no-cache');

    if($allow_cors) {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Credentials: true');
    }

    //see http://www.html5rocks.com/en/tutorials/eventsource/basics/?redirect_from_locale=de -> Controlling the Reconnection-timeout
    echo 'retry: '.($client_reconnect*1000)."\n"; //set the retry interval for the client
  }

  public static function send_message($id, $message, $progress = 0) {
    if( php_sapi_name() == 'cli' ) {
      echo "[$progress] $message\n";
      return;
    }

    $d = array('message' => $message , 'progress' => $progress);

    echo "id: $id" . PHP_EOL;
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;

    ob_flush();
    flush();
  }
}
?>
