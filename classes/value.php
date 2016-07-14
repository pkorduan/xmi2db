<?php
class Value {

	function Value($values) {
		$this->values = $values;
	}

	function asSql() {
		$sql = '(' .
			implode(
				', ',
				array_map(
					function($value) {
						switch (gettype($value)) {
							case 'string' :
								switch (true) {
									case (in_array(strtolower($value), array(
										'true',
										't',
										))) : 
										$sql = "'true'";
									break;
									case (in_array(strtolower($value), array(
										'false',
										'f',
										))) :
										$sql = "'false'";
									break;
									default:
										$sql = "'" . $value . "'";
								}
							break;
							case 'boolean' :
								$sql = ($value) ? "'true'" : "'false'";
							break;
							default:
								$sql = $value;
						}
						return $sql;
					},
					$this->values
				)
			) . ')';
		return $sql;
	}
}
?>