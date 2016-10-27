<?php
	# Schemas of ISO
	$schemas = array();
	$schemas[] = 'iso1';
	$schemas[] = 'iso2';
	$schemas[] = 'iso_test';
	$schemas[] = 'iso_test_names';
	$schemas[] = 'iso_test_dq';
	define('SCHEMAS', "'" . implode("';'", $schemas) . "'");
	
	# Packages of ISO Schema
	#	$packages[] = 'CadastralParcels';
	if ($packages) define('PACKAGES', "'" . implode("';'", $packages) . "'");
?>