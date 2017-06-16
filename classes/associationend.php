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
COMMENT ON COLUMN " . $this->parent_name . "." . $this->name . " IS 'Assoziation zu: ";
    $sql .= trim($this->stereotype . ' ' . $this->zeigt_auf_alias . ' (' . $this->zeigt_auf_name . ')');
    $sql .= ' ' . $this->multiplicity;
    $sql .= "';";
    return $sql;
  }

  function setUmlSchema($schema) {
    $this->umlSchema = $schema;
  }

  function get_database_type($with_enum_type = true, $with_codelist_type = true) {
    return PG_CHARACTER_VARYING;
  }

  function asGfs() {
    $gfs = "
    <PropertyDefn>
      <Name>" . $this->name . "</Name>
      <ElementPath>" . $this->alias . "</ElementPath>
      <Type>" . $this->get_gfs_type($this->get_database_type(false, false), $this->getBrackets()) . "</Type>
    </PropertyDefn>";
    return $gfs;
  }
}
?>
