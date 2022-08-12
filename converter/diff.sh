#!/bin/bash

set -e

UMLSCHEMA=aaa_uml_neu bash converter/generateschema.sh

for i in ~/src/alkis-import/alkis-schema.sql /tmp/alkis-schema-aaa_uml_neu.sql; do
	perl converter/sorttables.pl $i >$i.sorted
done

vimdiff ~/src/alkis-import/alkis-schema.sql.sorted /tmp/alkis-schema-aaa_uml_neu.sql.sorted
