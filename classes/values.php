<?php
class Values {

	function __construct() {
	}

	function addValue($value) {
		$this->values[] = $value;
	}


	function asSql() {
		$sql = '(' .
			implode(
				', ',
				array_map(
					function($value) {
						switch (gettype($value['key'])) {
							case 'string' :
								switch (true) {
									case (in_array(strtolower($value['key']), array(
										'true',
										't',
										))) : 
										$sql = "'true'";
									break;
									case (in_array(strtolower($value['key']), array(
										'false',
										'f',
										))) :
										$sql = "'false'";
									break;
									default:
										$sql = "'" . $value['key'] . "'";
								}
							break;
							case 'boolean' :
								$sql = ($value['key']) ? "'true'" : "'false'";
							break;
							default:
								$sql = $value['key'];
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