<?php
class Attribute {

	function Attribute($name, $type = 'text', $null = '', $default = '', $comment = '') {
		$this->name = $name;
		$this->type = $type;
		$this->null = $null;
		$this->default = $default;
		$this->comment = $comment;
	}

	function asSql() {
		$sql = "	" .
			$this->name . " " . $this->type;

		# Ausgabe NOT NULL
		if ($this->null != '')
			$sql .= ' ' . $this->null;

		# Ausgabe DEFAULT
		if ($this->default != '')
			$sql .= ' DEFAULT ' . $this->default;

		return $sql;
	}
}
?>