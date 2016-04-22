<?php
class Debug {
  function debug($debug) {
    $this->debug = $debug;
  }

  function write($text) {
    if ($this->debug) {
      echo '<br>' . $text;
    }
  }
}
?>