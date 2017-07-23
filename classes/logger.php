<?php
class Logger {
  function Logger($level = 0, $filename = '/tmp/xmi2db.log') {
    $this->level = $level;
    $this->debug = false;
    $this->text = '';
    if ($level == 2) {
      $this->file = fopen($filename, 'w');
    }
  }

  function log($text, $indent = 0, $log_always = false) {
    $space = '';
    for ($i = 0; $i < $indent; $i++) {
      $space .= '&nbsp;&nbsp;';
    }
    $this->text[] = $text;
    if ($this->level > 0 or $log_always) {
      if ($indent > 0) {
        $text = str_replace('<br>', '<br>' . $space . '|--', $text);
      }
      echo $text;
    }
    if ($this->level > 1)
      fwrite($this->file, $text);
  }

  function close() {
    if ($this->level == 2)
      $this->file->close();
  }
}
?>
