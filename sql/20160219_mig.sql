-- View: schema_name.classes_attributes_types

DROP VIEW schema_name.classes_attributes_types CASCADE;

CREATE OR REPLACE VIEW schema_name.classes_attributes_types AS 
 SELECT t1.class_name,
    t1.atribute_name,
    t1.class_xmi_id,
    t1.class_id,
    t1.attribute_xmi_id,
    t1.attribute_id,
    t1.package_id,
    COALESCE(t2.name, ''::character varying)::text || COALESCE(t4.name, ''::character varying)::text AS datatype,
    t1.attribute_datatype,
    t3.name AS classifier,
    t1.attribute_classifier
   FROM schema_name.classes_attributes t1
     LEFT JOIN schema_name.datatypes t2 ON t1.attribute_datatype::text = t2.xmi_id::text
     LEFT JOIN schema_name.datatypes t4 ON t1.attribute_classifier::text = t4.xmi_id::text
     LEFT JOIN schema_name.uml_classes t3 ON t1.attribute_classifier::text = t3.xmi_id::text;

ALTER TABLE schema_name.classes_attributes_types
  OWNER TO pgadmin;

-- View: schema_name.classes_attributes_types_gen

-- DROP VIEW schema_name.classes_attributes_types_gen;

CREATE OR REPLACE VIEW schema_name.classes_attributes_types_gen AS 
 SELECT generalizations.parent_name AS gen_name,
    classes_attributes_types.class_name,
    generalizations.child_id,
    generalizations.comment,
    classes_attributes_types.atribute_name,
    classes_attributes_types.class_xmi_id,
    classes_attributes_types.class_id,
    classes_attributes_types.attribute_xmi_id,
    classes_attributes_types.attribute_id,
    classes_attributes_types.package_id,
    classes_attributes_types.datatype,
    classes_attributes_types.attribute_datatype,
    classes_attributes_types.classifier,
    classes_attributes_types.attribute_classifier
   FROM schema_name.classes_attributes_types
     LEFT JOIN schema_name.generalizations ON classes_attributes_types.class_xmi_id::text = generalizations.child_id::text;

ALTER TABLE schema_name.classes_attributes_types_gen
  OWNER TO pgadmin;
  
-- View: schema_name.full_model

-- DROP VIEW schema_name.full_model;

CREATE OR REPLACE VIEW schema_name.full_model AS 
 SELECT "packages_parent-name".parent_package_name,
    "packages_parent-name".name AS package_name,
    classes_attributes_types_gen.gen_name,
    classes_attributes_types_gen.class_name,
    classes_attributes_types_gen.atribute_name,
    classes_attributes_types_gen.datatype,
    classes_attributes_types_gen.classifier,
    classes_attributes_types_gen.comment
   FROM schema_name.classes_attributes_types_gen,
    schema_name."packages_parent-name"
  WHERE classes_attributes_types_gen.package_id = "packages_parent-name".id
  ORDER BY "packages_parent-name".name, classes_attributes_types_gen.class_name;

ALTER TABLE schema_name.full_model
  OWNER TO pgadmin;