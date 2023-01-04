<?php
class AssociationEnd extends Attribute {

  function __construct($name, $parent_name, $zeigt_auf_name, $logger) {
    $this->name = strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
    $this->alias = $name;
    $this->parent_name = strtolower(substr($parent_name, 0, PG_MAX_NAME_LENGTH));
    $this->parent_name_alias = $parent_name;
    $this->comments = array();
    $this->stereotype = '';
    $this->zeigt_auf_name = strtolower(substr($zeigt_auf_name, 0, PG_MAX_NAME_LENGTH));
    $this->zeigt_auf_alias = $zeigt_auf_name;
    $this->multiplicity = '';
    $this->multiplicity_lower = '';
    $this->multiplicity_upper = '';
    $this->logger = $logger;
    $this->null = '';
    $this->default = '';
  }

  function getComment($parent_name) {
    $sql = "
COMMENT ON COLUMN " . $parent_name . "." . $this->name . " IS 'Assoziation zu: ";
    $sql .= trim($this->stereotype . ' ' . $this->zeigt_auf_alias . ' (' . $this->zeigt_auf_name . ')');
    $sql .= ' ' . $this->multiplicity;
    $sql .= "';";
    return $sql;
  }

  function setUmlSchema($schema) {
    $this->umlSchema = $schema;
  }

  function get_database_type($with_enum_type = true, $with_codelist_type = true) {
    if( $this->stereotype == "FeatureType" ) {
			switch (true) {
				case in_array($this->name, array(
						GEOMETRY_COLUMN_NAME
					)):
					$database_type = 'geometry';
				break;
				
				default : {
					$database_type = PG_GML_ID;
				}
			}
    }
    else {
      $database_type = PG_CHARACTER_VARYING;
    }
		return $database_type;
  }
	
  function getBrackets() {
    $brackets = false;
    if (is_array($this->parts) and !empty($this->parts)) {
      $brackets = in_array(
        '[]',
        array_map(
          function($attribute) {
            return ($attribute->multiplicity_upper == '*' OR $attribute->multiplicity == '*' OR intval($attribute->multiplicity) > 1) ? '[]' : '';
          },
          $this->parts
        )
      );
    }
    else {
      $brackets = ($this->multiplicity_upper == '*' OR $this->multiplicity == '*' OR intval($this->multiplicity) > 1);
    }
    return ($brackets AND !in_array($this->name, array(GEOMETRY_COLUMN_NAME))) ? '[]' : '';
  }	

  function asGfs() {
		if($this->name != GEOMETRY_COLUMN_NAME){
			$type = $this->get_gfs_type($this->get_database_type(false, false), $this->getBrackets());
			$gfs = "
			" . (is_numeric($type) ? '<GeomPropertyDefn>' : '<PropertyDefn>') . "
				<Name>" . $this->name . "</Name>
				<ElementPath>" . (is_numeric($type) ? 'position' : $this->alias) . "</ElementPath>
				<Type>" . $this->get_gfs_type($this->get_database_type(false, false), $this->getBrackets()) . "</Type>
			" . (is_numeric($type) ? '</GeomPropertyDefn>' : '</PropertyDefn>');
			return $gfs;
		}
  }

  function getIndex($name) {
    if( $this->getBrackets() == "[]" ) {
      $using = "USING gin (" . $this->name . ")";
    } else {
      $using = "USING btree (" . $this->name . ")";
    }

    $table = $name;
    $attribute = $this->alias;
    $identifier = $table . "_" . $attribute;

    if(strlen($identifier) > PG_MAX_NAME_LENGTH) {
      // Gekürzer Tabellename & Alias
      $table = preg_replace('/[a-z]/', '', ucfirst($name));
      $identifier = $table . "_" . $this->name;
    }

    if(strlen($identifier) > PG_MAX_NAME_LENGTH) {
      // Tabellename & gekürzter Alias
      $attribute = preg_replace('/[a-z]/', '', ucfirst($this->alias));
      $identifier = $table . "_" . $attribute;
    }

    if(strlen($identifier) > PG_MAX_NAME_LENGTH) {
      // Gekürzter Tabellenname & gekürzer Alias
      $attribute = preg_replace('/[a-z]/', '', ucfirst($this->alias));
      $identifier = $table . "_" . $attribute;
    }

    return "
CREATE INDEX " . strtolower($identifier) . " ON " . strtolower($name) . " $using;";
  }
}
?>
