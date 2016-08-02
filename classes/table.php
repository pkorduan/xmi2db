<?php
class Table {

	function Table($name) {
		$this->alias = $name;
		$this->name = strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
		$this->comments = array();
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->attributes = array();
		$this->primaryKey = '';
		$this->inherits = '';
		$this->withOids = true;
		$this->values = new Data();
	}

	function addAttribute($attribute) {
		$this->attributes[] = $attribute;
	}

	function getKeys() {
		return array_map(
			function($attribute) {
				return $attribute['name'];
			},
			$this->attributes
		);
	}

	function addComment($comment) {
		$this->comments[] = $comment;
	}

	function asSql() {
		$sql = "
CREATE TABLE IF NOT EXISTS " . $this->name . " (
";

		# Ausgabe Attribute
		$i = 0;
		while ($i < count($this->attributes)) {
			$sql .= $this->attributes[$i]->asSql();
			$i++;
			if ($i < count($this->attributes))
				$sql .= ",\n";
		}

		# Ausgabe Primary Key
		if ($this->primaryKey != '')
			$sql .= ",\n	CONSTRAINT " . $this->name . '_pkey PRIMARY KEY (' . $this->primaryKey . ')';

		$sql .= '
)';

		# Ausgabe Vererbung
		if ($this->inherits != '')
			$sql .= ' INHERITS (' . $this->inherits . ')';

		# Ausgabe WITH OIDS
		if ($this->withOids)
			$sql .= ' WITH OIDS';

		$sql .= ';
';	# Tabellenende

		# Ausgabe Tabellenkommentare
		if (!empty($comments)) {
			$sql .= "\nCOMMENT ON TABLE " . $this->name . " IS '" .
				implode(', ', $this->comments) . "';";
		}

		# Ausgabe Tabellen Values
		if (!empty($this->values->rows)) {
			$sql .= "\nINSERT INTO " . $this->name . ' (' .
				implode(
					',',
					array_map(
						function($attribute) {
							return $attribute->name;
						},
						$this->attributes
					)
				) .
			") VALUES \n";
			$sql .= $this->values->asSql();
			$sql .= ';';
		}

		return $sql;
	}

}
?>