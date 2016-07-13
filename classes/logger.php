<?php
class Logger {

	function Logger($level = 0, $filename = '/tmp/xmi2db.log') {
		$this->level = $level;
		$this->text = '';
		if ($level == 2) {
			$this->file = fopen($filename, '+w');
		}
	}

	function log($text) {
		$this->text[] = $text;
		if ($this->level == 1)
			echo $text;
		if ($this->level == 2)
			$this->file->write($text);
	}
}
?>