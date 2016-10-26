<?php
	# Schemas of Inspire
	$schemas = array();
	$schemas[] = 'inspire1';
	$schemas[] = 'inspire2';
	$schemas[] = 'inspire_test';
	define('SCHEMAS', "'" . implode("';'", $schemas) . "'");
	# Packages of INSPIRE Schema

	#	$packages[] = 'CadastralParcels';
	
	define('PACKAGES', "'" . implode("';'", $packages) . "'");
?>