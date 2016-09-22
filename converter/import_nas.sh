#!/bin/bash
DATA_PATH="/var/www/data/alkis"
OGR_PATH="/usr/local/bin"
ERROR_LOG=${DATA_PATH}/logs/postnas_err.log
cd ${DATA_PATH}
unzip ${DATA_PATH}/*.zip
rm ${DATA_PATH}/*.zip
gunzip ${DATA_PATH}/*.xml.gz
cd $DATA_PATH
FILES=*.xml
FIRST_FILE="YES"
rm import.xml
rm $ERROR_LOG

for NAS_FILE in $FILES
do
  if [ -z $FIRST_FILE ] ; then
    echo "Processing $NAS_FILE file..."
    cp ${NAS_FILE} import.xml
    export PGPASSWORD="secure"
    ${OGR_PATH}/ogr2ogr -f "PostgreSQL" --config PG_USE_COPY NO -nlt CONVERT_TO_LINEAR -append PG:"dbname=kvwmapsp active_schema=alkis user=kvwmap host=pgsql port=5432" -a_srs EPSG:25833 import.xml 2>> $ERROR_LOG
    if [ -n "$(grep 'ERROR' logs/postnas_err.log)" ] ; then
      echo ""
      echo `date`": Fehler ist aufgetreten beim Einlesen der Datei ${NAS_FILE}." >> $ERROR_LOG
      tail -n100 $ERROR_LOG
      break
    fi
  else
    echo "Ignore file $NAS_FILE ..."
    FIRST_FILE=""
  fi;
  mv ${NAS_FILE} ./archiv/${NAS_FILE}
  gzip ./archiv/${NAS_FILE}
done
#echo "Postprozessing ausführen."
#psql -h pgsql -U kvwmap -f ${DATA_PATH}/postprocessing/pp_laden_ohne_alkis_beziehungen.sql kvwmapsp
#echo "Nutzungsarten laden."
#psql -h pgsql -U kvwmap -f ${DATA_PATH}/postprocessing/nutzungsart_laden.sql kvwmapsp