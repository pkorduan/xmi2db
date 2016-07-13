<?php
class Attribute {

	function Attribute($name, $type = 'text', $null = '', $default = '', $comment = '') {
		$this->alias = $name;
		$this->name = strtolower(substr($name, 0, 58));
		$this->brackets = '';

		if (substr($type, -2) == '[]') {
			$brackets = '[]';
			$type = substr($type, 0, -2);
		}
		$this->type_alias = $type; # langer Name
		$this->type = strtolower(substr($type, 0, 58)); # verkürzter Name

		$this->null = $null;
		$this->default = $default;
		$this->comment = $comment;
	}

	function asSql() {
		$sql = "	" .
			$this->name . " " . $this->type . $this->brackets;

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