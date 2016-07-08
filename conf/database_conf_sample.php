<?php
  define('DEBUG', false);
  $db_server = "localhost"; // Hostname
  $db_user = "pgadmin"; // Benutzername
  $db_pass = "PaGeMin2"; // Kennwort
  $db_name = "moro"; // Name der Datenbank
  $db_conn  = pg_connect("host=".$db_server." dbname=".$db_name." user=".$db_user." password=".$db_pass)  or exit ("Es konnte keine Verbindung zum Datenbankserver hergestellt werden.");
  define('UML_SCHEMA', 'xplan_uml');
  define('CLASSES_SCHEMA', 'gml_classes');
  $packages[] = 'Basisklassen';
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
  $packages[] = 'Raumordnungsplan';
  $packages[] = 'RP__Basisobjekte';
  $packages[] = 'RP_Freiraumstruktur';
  $packages[] = 'RP_Infrastruktur';
  $packages[] = 'RP_Raster';
  $packages[] = 'RP_Siedlungsstruktur';
  $packages[] = 'RP_Sonstiges';
#  $packages[] = 'SO_Basisobjekte';
#  $packages[] = 'SO_NachrichtlicheUebernahmen';
#  $packages[] = 'SonstigePlanwerke';
#  $packages[] = 'SO_Raster';
#  $packages[] = 'SO_Schutzgebiete';
#  $packages[] = 'SO_SonstigeGebiete';
#  $packages[] = 'SO_Sonstiges';
  $packages[] = 'XP_Basisobjekte';
  $packages[] = 'XP_Enumerationen';
  $packages[] = 'XP_Praesentationsobjekte';
  $packages[] = 'XP_Raster';
  define('PACKAGES', "'" . implode("','", $packages) . "'");
  
  $classesOut[] = '';
  #$classesOut[] = 'AX_Flurstueck';
  #$classesOut[] = 'AX_BenutzergruppeMitZugriffskontrolle';
  #$classesOut[] = 'AX_Gebaeude';
  #$classesOut[] = 'AX_Gebiet_Verwaltungsgemeinschaft';
  #$classesOut[] = 'AX_MarkanterGelaendepunkt';
  #$classesOut[] = 'AA_Objekt';
  #$classesOut[] = 'AX_HistorischesFlurstueckOhneRaumbezug';
  #$classesOut[] = 'AX_Verwaltungsgemeinschaft';
  #$classesOut[] = 'AX_Schleuse';
  
  $attributesOut[] = '';
  #$attributesOut[] = 'gemarkung';
  #$attributesOut[] = 'gemeindezugehoerigkeit';
  #$attributesOut[] = 'qualitaetsangaben';
?>