# xmi2db
Convert UML class models from XMI files into a relational database models (testet for PostgreSQL).

XMI files are XML representations of UML models. Sometimes there is a need to have theses UML model elements especially the classes its attributes, the assosiations an generalisations in a database table structure.
xmi2db read an xmi file and write the UML Things into a database structure, which is oriented on the UML Structure. There are tables fore uml_classes, uml_attributes, class_generalizsations, datatypes, stereotypes etc.
In a second step xmi2db produce a relational database schema which have separate tables for each class and attributes in accordance to the type defined in uml. The conversion from UML datatyps into types of the data base can be easy adapted. The definition of tables consider the generalization of UML classes and uses inheritance. Multiplicity are accounted by defining array types of attribute. The assosiations will be realised by autogenerated gml_id attributes of type uuid for each superclass table.
Our xmi2db converter focus on UML models of GML application schemas used eg. for [INSPIRE](http://inspire.ec.europa.eu/data-model/approved/r4618-ir/html/) data specifications or German application schemas of the [AAA project](http://www.adv-online.de/AAA-Modell/) or [XPlanung](http://www.iai.fzk.de/www-extern/index.php?id=679). Therefore type UNION will be converted to a geometry type and the PostGIS extension is neccesary in our database model.
We use it especially to create database models derived from the currently frequently changing XPlanung UML model to be able to store and manage XPlanung features in a PostgreSQL database.

Installation

* Clone the project into your web folder.
```
git clone https://github.com/pkorduan/xmi2db.git
```
* Create and edit the database config
```
cp conf/database_conf_sample.php conf/database_conf.php
```
* Edit database credentials PG_HOST, PG_USER, PG_PASSWORD, PG_DBNAME

* Create a database owned by $PG_USER extension PostGIS

* Copy your xmi file into the subfolder xmis

* Then you can open the converter gui.
http://yourserver.de/xmi2db/

First run xmi2db "Fülle DB mit XMI Inhalten" to fill the uml_schema with Metadata about your UML model
* Select the xmis file you copied previously into the xmis folder,
* Select the schema egg. xplan_uml
* Select the base package of the UML model, in case of XPlanGML application schema "XPlanGML 4.1"
* Click on "Fülle DB mit XMI Inhalten"
You can also predefine the schema names in conf/database_config.php

This application was originally developed by Christian Seip and Peter Korduan separately and will now be maintaint together in this git repo.

This version is the first draft and in development.