#!/bin/bash

set -e

cleanup() {
	#sed -e "s/<br>/\n/g" -e "s/<i>/$(tput ritm)/g" -e "s/<\/i>/$(tput ritm)/" -e "s/<b>/$(tput smso)/g" -e "s/<\/b>/$(tput rmso)/" -e "s/<[^>]*>//g"
	tee /tmp/log |
	sed -r \
		-e "s/  */ /g" \
		-e "s/<textarea[^>]*>/\n/" \
		-e "s/<br>/\n/g" \
		-e "s/<tr[^>]*>/\n/g" \
		-e "s/<t[hd][^>]*>/	/g" \
		-e "s/<[^>]*>//g"
}

pushd converter || { echo convert not found; exit 1; }

[ -z "$UMLSCHEMA" ] && echo "UMLSCHEMA not found" && exit 1

if [ -n "$XMI" ]; then
	pwd
	ls ../xmis/$XMI
	[ -f "../xmis/$XMI" ] || { echo $XMI not found; exit 1; }
	php xmi2db.php \
		umlSchema=$UMLSCHEMA \
		truncate=1 \
		basepackage= \
		file=../xmis/$XMI \
		argo=0 |
		cleanup |
		tee /tmp/xmi2db-xmi2db.log
fi


php-cgi -q db2ogr.php \
	umlSchema=$UMLSCHEMA \
	epsgCode=:alkis_epsg \
	ogrSchema=':"alkis_schema"' \
	withCodeLists=1 \
	loglevel=2 \
	>/tmp/alkis-schema-$UMLSCHEMA.sql

cleanup </tmp/xmi2db.log >/tmp/xmi2db-ogr-$UMLSCHEMA.log

php-cgi -q db2gfs.php \
	umlSchema=$UMLSCHEMA \
	epsgCode=:alkis_epsg \
	loglevel=2 \
	>/tmp/alkis-schema-$UMLSCHEMA.gfs

cleanup </tmp/xmi2db.log >/tmp/xmi2db-gfs-$UMLSCHEMA.log
