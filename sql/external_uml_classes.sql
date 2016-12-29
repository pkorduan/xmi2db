INSERT INTO schema_name.uml_classes (name, stereotype_id)
VALUES
  ('DQ_AbsoluteExternalPositionalAccuracy', (SELECT xmi_id FROM aaa_uml.stereotypes WHERE name = 'DataType')),
  ('DQ_RelativeInternalPositionalAccuracy', (SELECT xmi_id FROM aaa_uml.stereotypes WHERE name = 'DataType'));