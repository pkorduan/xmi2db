<?php
class Attribute {

	function __construct($name, $datatype, $parent = '', $parts = '', $null = '', $default = '', $comment = '') {
		$this->alias = $name;
		$this->name = $this->getName($name);
		$this->brackets = '';
		$this->parent = $parent;
		$this->parts = parts;
		$this->datatype = strtolower(substr($datatype, 0, PG_MAX_NAME_LENGTH));
		$this->datatype_alias = $datatype;
		$this->attribute_type = '';
		$this->stereotype = '';
		$this->stereotype_alias = '';
		$this->multiplicity = '';
		$this->multiplicity_lower = '';
		$this->multiplicity_upper = '';
		$this->database_type = '';

		$this->database_type_alias = $database_type; # langer Name
		$this->database_type = strtolower(substr($database_type, 0, 58)); # verkürzter Name

		$this->null = $null;
		$this->default = $default;
		$this->comment = $comment;
		$this->attributes_name = '';
		$this->path_name = '';
		$this->frequency = 0;
	}

	public static function getName($name) {
		return strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
	}

	function setNameFromParts() {
		$this->path_name = implode(
			'_',
			array_map(
				function($part) {
					return $part->parent->alias . '_' . $part->alias;
				},
				$this->parts
			)
		);

		$this->attributes_name = implode(
			'_',
			array_map(
				function($part) {
					return $part->name;
				},
				$this->parts
			)
		);

		$this->short_name = end($this->parts)->name;
	}

	function getComment($table_name) {
		$sql = "
COMMENT ON COLUMN " . $table_name . "." . $this->name . " IS '";
		$sql .= trim($this->name . ' ' . $this->stereotype_alias . ' ' . $this->datatype_alias);
		$sql .= ' ' . $this->multiplicity;
		$sql .= "';";
		return $sql;
	}

	function getFlattenedComment($table_name) {
		$parts = $this->parts;
		$attribute_path = $parts[0]->alias;
		array_shift($parts);
		$attribute_path .= ' ' . implode(
			'|',
			array_map(
				function($part) {
					return $part->parent->alias . '|' . $part->alias;
				},
				$parts
			)
		);
		$sql = "
COMMENT ON COLUMN " . $table_name . "." . $this->short_name . " IS '";
		$sql .= $attribute_path . ' ' . $this->stereotype_alias . ' ' . $this->datatype_alias;
		$sql .= ' ' . $this->multiplicity;
		$sql .= "';";
		return $sql;
	}

	function setMultiplicity($lower, $upper) {
		$this->multiplicity_lower = ($lower == '-1' OR intval($lower) > 1) ? '*' : $lower;
		$this->multiplicity_upper = ($upper == '-1' OR intval($upper) > 1) ? '*' : $upper;
		if ($this->multiplicity_lower == $this->multiplicity_upper)
			$this->multiplicity = $this->multiplicity_lower;
		else {
			if (empty($this->multiplicity_lower))
				$this->multiplicity_lower = '0';
			$this->multiplicity = $this->multiplicity_lower . '..' . $this->multiplicity_upper;
		}
		return $this->multiplicity;
	}

	function setStereoType($stereotype) {
		$this->stereotype = strtolower(substr($stereotype, 0, PG_MAX_NAME_LENGTH));
		$this->stereotype_alias = $stereotype;
	}

	function get_database_type() {
		$database_type = $this->datatype;

		if (in_array($this->stereotype, array(
			'datatype',
			'codelist',
			'enumeration',
			'union'
		))) {
			$database_type = $this->datatype;
		}
		else {
			switch (true) {
				# text
				case in_array($this->datatype, array(
						'',
						'featurecollection',
						'li_lineage',
						'sequence',
						'dq_relativeinternalpositionalaccuracy',
						'dq_absoluteexternalpositionalaccuracy',
						'genericname'
					)) :
					$database_type = 'text';
				break;

				# character varying
				case in_array($this->datatype, array(
						'characterstring',
						'<undefined>',
						'enumeration',
						'enum',
						'uri'
					)) :
					$database_type = 'character varying';
				break;

				# date
				case in_array($this->datatype, array(
						'date',
						'datetime',
						'tm_duration'
					)) :
					$database_type = 'date';
				break;

				# integer
				case in_array($this->datatype, array(
						'integer',
						'int',
						'codelist'
					)):
					$database_type = 'integer';
				break;

				# boolean
				case ($this->datatype == 'boolean'):
					$database_type = 'boolean';
				break;

				# double precision
				case in_array($this->datatype, array(
						'angle',
						'length',
						'decimal',
						'volume',
						'area',
						'real',
						'distance'
					)):
					$database_type = 'double precision';
				break;

				# uuid
				case in_array($this->datatype, array(
						'datatype'
					)):
					$database_type = 'uuid';
				break;

				# geometry
				case in_array($this->datatype, array(
						'gm_point',
						'directposition'
					)):
					$database_type = 'geometry(POINT)';
				break;

				case ($this->datatype == 'gm_curve'):
					$database_type = 'geometry(LINESTRING)';
				break;

				case ($this->datatype == 'gm_compositecurve'):
					$database_type = 'geometry(MULTILINESTRING)';
				break;

				case ($this->datatype == 'gm_multicurve'):
					$database_type = 'geometry(MULTILINESTRING)';
				break;

				case ($this->datatype == 'gm_multipoint'):
					$database_type = 'geometry(MULTIPOINT)';
				break;

				case ($this->datatype == 'gm_triangulatedsurface'):
					$database_type = 'geometry(MULTIPOLYGON)';
				break;

				case ($this->datatype == 'gm_solid'):
					$database_type = 'geometry(MULTIPOLYGON)';
				break;

				case ($this->datatype == 'gm_compositesolid'):
					$database_type = 'geometry(MULTIPOLYGON)';
				break;

				case ($this->datatype == 'gm_multisurface'):
					$database_type = 'geometry(MULTIPOLYGON)';
				break;

				case ($this->datatype == 'gm_surface'):
					$database_type = 'geometry(POLYGON)';
				break;

				case in_array($this->datatype, array(
						'gm_object',
						'union'
					)):
					$database_type = 'geometry';
				break;
			} # end of switch
		}

		if ($database_type == '')
			$database_type = 'text';

		$this->database_type = $database_type;
		return $database_type;
	}
	
	function getBrackets() {
		return ($this->multiplicity_upper == '*' OR $this->multiplicity == '*' OR intval($this->multiplicity) > 1) ? '[]' : '';
	}

	function asSql() {
		$sql = "	" .
			$this->name . " " . $this->get_database_type() . $this->getBrackets();

		# Ausgabe NOT NULL
		if ($this->null != '')
			$sql .= ' ' . $this->null;

		# Ausgabe DEFAULT
		if ($this->default != '')
			$sql .= ' DEFAULT ' . $this->default;

		return $sql;
	}

	function asFlattenedSql() {
		$sql = "	" .
			$this->short_name . " " . $this->get_database_type() . $this->getBrackets();

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