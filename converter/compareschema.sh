#!/bin/bash

set -e
[ -z "$UMLSCHEMA" ] && echo "UMLSCHEMA not found" && exit 1
[ -f "/tmp/alkis-schema-$UMLSCHEMA.sql" ] || { echo "/tmp/alkis-schema-$UMLSCHEMA.sql not found"; exit 1; }

ORIGSCHEMA=aaa_orig
NEWSCHEMA=aaa_new

if ! [ -f .new-created ] || [ "/tmp/alkis-schema-$UMLSCHEMA.sql" -nt .new-created ]; then
	if ! [ -f .orig-created ]; then
		psql -q -v alkis_epsg=25832 -v parent_schema=public -v postgis_schema=public -v alkis_schema=$ORIGSCHEMA service=xmi2db <<EOF
\set ON_ERROR_STOP
DROP SCHEMA IF EXISTS :"alkis_schema" CASCADE;
CREATE SCHEMA :"alkis_schema";
SET search_path = :"alkis_schema", public;
\i ~/src/alkis-import/alkis-schema.sql
EOF
		touch -r ~/src/alkis-import/alkis-schema.sql .orig-created
	fi

	psql -q -v alkis_epsg=25832 -v parent_schema=aaa_xmi2db -v postgis_schema=public -v alkis_schema=$NEWSCHEMA service=xmi2db <<EOF
\set ON_ERROR_STOP
DROP SCHEMA IF EXISTS :"alkis_schema" CASCADE;
CREATE SCHEMA :"alkis_schema";
SET search_path = :"alkis_schema", public;
\i /tmp/alkis-schema-$UMLSCHEMA.sql
EOF

	touch -r /tmp/alkis-schema-$UMLSCHEMA.sql .new-created
fi

perl converter/compare.pl $ORIGSCHEMA $NEWSCHEMA $UMLSCHEMA 2>&1 | tee /tmp/schema.diff
