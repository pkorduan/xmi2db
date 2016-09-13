<?php
	error_reporting(E_ALL & ~E_NOTICE);

	$loglevel = ($_REQUEST['loglevel'] != '') ? $_REQUEST['loglevel'] : '0';
	define('LOGLEVEL', $loglevel);

	define('PG_HOST', 'localhost'); // Hostname
	define('PG_DBNAME', 'postgres'); // Name der Datenbank
	define('PG_USER', 'postgres'); // Benutzername
	define('PG_PASSWORD', 'postgres'); // Kennwort
	define('PG_MAX_NAME_LENGTH', 58); // Maximale Länge von Tabellen, Type und Attributnamen

	$umlSchemaVar = ($_REQUEST['umlSchema'] != '') ? $_REQUEST['umlSchema'] : 'aaa_uml';
	$gmlSchemaVar = ($_REQUEST['gmlSchema'] != '') ? $_REQUEST['gmlSchema'] : 'aaa_gml';
	$ogrSchemaVar = ($_REQUEST['ogrSchema'] != '') ? $_REQUEST['ogrSchema'] : 'aaa_ogr';

	define('UML_SCHEMA', $umlSchemaVar);
	define('CLASSES_SCHEMA', $gmlSchemaVar);
	define('OGR_SCHEMA', $ogrSchemaVar);

	define('WITH_UUID_OSSP', false);

	define('FILTER_FILE','../conf/filter.json');

	$packages = array();

# Packages of XPlanung Schema
#  $packages[] = 'Basisklassen';
#  $packages[] = 'Bebauungsplan';
#  $packages[] = 'BP_Aufschuettung_Abgrabung_Bodenschaetze';
#  $packages[] = 'BP__Basisobjekte';
#  $packages[] = 'BP_Bebauung';
#  $packages[] = 'BP_Erhaltungssatzung_und_Denkmalschutz';
#  $packages[] = 'BP_Gemeinbedarf_Spiel_und_Sportanlagen';
#  $packages[] = 'BP_Landwirtschaft, Wald- und Grünflächen';
#  $packages[] = 'BP_Naturschutz_Landschaftsbild_Naturhaushalt';
#  $packages[] = 'BP_Raster';
#  $packages[] = 'BP_Sonstiges';
#  $packages[] = 'BP_Umwelt';
#  $packages[] = 'BP_Verkehr';
#  $packages[] = 'BP_Ver_und_Entsorgung';
#  $packages[] = 'BP_Wasser';
#  $packages[] = 'Flaechennutzungsplan';
#  $packages[] = 'FP_Aufschuettung_Abgrabung_Bodenschaetze';
#  $packages[] = 'FP__Basisobjekte';
#  $packages[] = 'FP_Bebauung';
#  $packages[] = 'FP_Gemeinbedarf_Spiel_und_Sportanlagen';
#  $packages[] = 'FP_Landwirtschaft_Wald_und_Gruen';
#  $packages[] = 'FP_Naturschutz';
#  $packages[] = 'FP_Raster';
#  $packages[] = 'FP_Sonstiges';
#  $packages[] = 'FP_Verkehr';
#  $packages[] = 'FP_Ver- und Entsorgung';
#  $packages[] = 'FP_Wasser';
#  $packages[] = 'Landschaftsplan_Kernmodell';
#  $packages[] = 'LP__Basisobjekte';
#  $packages[] = 'LP__Erholung';
#  $packages[] = 'LP__MassnahmenNaturschutz';
#  $packages[] = 'LP__Raster';
#  $packages[] = 'LP__SchutzgebieteObjekte';
#  $packages[] = 'LP__Sonstiges';
#  $packages[] = 'Raumordnungsplan';
#  $packages[] = 'RP__Basisobjekte';
#  $packages[] = 'RP_Freiraumstruktur';
#  $packages[] = 'RP_Infrastruktur';
#  $packages[] = 'RP_Raster';
#  $packages[] = 'RP_Siedlungsstruktur';
#  $packages[] = 'RP_Sonstiges';
#  $packages[] = 'SO_Basisobjekte';
#  $packages[] = 'SO_NachrichtlicheUebernahmen';
#  $packages[] = 'SonstigePlanwerke';
#  $packages[] = 'SO_Raster';
#  $packages[] = 'SO_Schutzgebiete';
#  $packages[] = 'SO_SonstigeGebiete';
#  $packages[] = 'SO_Sonstiges';
#  $packages[] = 'XP_Basisobjekte';
#  $packages[] = 'XP_Enumerationen';
#  $packages[] = 'XP_Praesentationsobjekte';
#  $packages[] = 'XP_Raster';

# Packages of AAA Schema
	$packages[] = 'AAA Basisschema';
	$packages[] = 'AAA_Basisklassen';
	$packages[] = 'AAA_GemeinsameGeometrie';
	$packages[] = 'AAA_Nutzerprofile';
	$packages[] = 'AAA_Operationen';
	$packages[] = 'AAA_Praesentationsobjekte';
	$packages[] = 'AAA_Praesentationsobjekte 3D';
	$packages[] = 'AAA_Projektsteuerung';
	$packages[] = 'AAA_Punktmengenobjekte';
	$packages[] = 'AAA_Spatial Schema';
	$packages[] = 'AAA_Spatial Schema 3D';
	$packages[] = 'AAA_Unabhaengige Geometrie';
	$packages[] = 'AAA_Unabhaengige Geometrie 3D';
	$packages[] = 'Codelisten';
	$packages[] = 'AFIS-ALKIS-ATKIS Fachschema';
	$packages[] = 'Bauwerke, Einrichtungen und sonstige Angaben';
	$packages[] = 'Bauwerke und Einrichtungen in Siedlungsflächen';
	$packages[] = 'Bauwerke, Anlagen und Einrichtungen für den Verkehr';
	$packages[] = 'Besondere Angaben zum Gewässer';
	$packages[] = 'Besondere Angaben zum Verkehr';
	$packages[] = 'Besondere Anlagen auf Siedlungsflächen';
	$packages[] = 'Besondere Eigenschaften von Gewässern';
	$packages[] = 'Besondere Vegetationsmerkmale';
	$packages[] = 'Eigentümer';
	$packages[] = 'Personen- und Bestandsdaten';
	$packages[] = 'Flurstücke, Lage, Punkte';
	$packages[] = 'Angaben zu Festpunkten der Landesvermessung';
	$packages[] = 'Angaben zum Flurstück';
	$packages[] = 'Angaben zum Netzpunkt';
	$packages[] = 'Angaben zum Punktort';
	$packages[] = 'Angaben zur Historie';
	$packages[] = 'Angaben zur Lage';
	$packages[] = 'Angaben zur Reservierung';
	$packages[] = 'Fortführungsnachweis';
	$packages[] = 'Gebäude';
	$packages[] = 'Angaben zum Gebäude';
	$packages[] = 'Gesetzliche Festlegungen, Gebietseinheiten, Kataloge';
	$packages[] = 'Administrative Gebietseinheiten';
	$packages[] = 'Bodenschätzung, Bewertung';
	$packages[] = 'Geographische Gebietseinheiten';
	$packages[] = 'Kataloge';
	$packages[] = 'Öffentlich-rechtliche und sonstige Festlegungen';
	$packages[] = 'Migration';
	$packages[] = 'Migrationsobjekte';
	$packages[] = 'Nutzerprofile';
	$packages[] = 'Angaben zu Nutzerprofilen';
	$packages[] = 'Relief';
	$packages[] = 'Primäres DGM';
	$packages[] = 'Reliefformen';
	$packages[] = 'Sekundäres DGM';
	$packages[] = 'Tatsächliche Nutzung';
	$packages[] = 'Gewässer';
	$packages[] = 'Siedlung';
	$packages[] = 'Vegetation';
	$packages[] = 'Verkehr';
	$packages[] = 'NAS-Operationen';
	$packages[] = 'AFIS-ALKIS-ATKIS-Ausgabekatalog';
	$packages[] = 'AFIS-ALKIS-ATKIS-Ausgaben';
	$packages[] = 'AFIS-Einzelpunktnachweise';
	$packages[] = 'AFIS-Punktlisten';
	$packages[] = 'ALKIS-Ausgaben';
	$packages[] = 'Komplexe Datentypen für Ausgaben';
	$packages[] = 'ALKIS-Auswertungen';
	$packages[] = 'Angaben im Kopf der Ausgaben';
	$packages[] = 'Externe Datentypen';
	$packages[] = 'Flurstücksangaben';
	$packages[] = 'Fortführungsfälle';
	$packages[] = 'Gebäudeangaben';
	$packages[] = 'Personen- und Bestandsangaben';
	$packages[] = 'Punktangaben';
	$packages[] = 'Reservierungen';

	define('PACKAGES', "'" . implode("','", $packages) . "'");
?>