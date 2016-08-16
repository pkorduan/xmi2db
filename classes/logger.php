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

	function log($text) {
		$this->text[] = $text;
		if ($this->level > 0)
			echo $text;
		if ($this->level > 1)
			fwrite($this->file, $text);
	}
	
	function close() {
		if ($this->level == 2)
			$this->file->close();
	}
}
?>