-- Function: schema_name.parent_classes
CREATE OR REPLACE FUNCTION schema_name.parent_classes(selected_child_id varchar)
	RETURNS TABLE (
		class_name varchar,
		depth int
	) AS
	$$
		BEGIN
			RETURN QUERY 
				SELECT
					*
				FROM
					(
						WITH RECURSIVE parent_childs(child_id, parent_id, depth) AS (
								SELECT child_id, parent_id, 1
								FROM schema_name.class_generalizations
								WHERE child_id = selected_child_id
							UNION ALL
								SELECT p.child_id, p.parent_id, pc.depth + 1
								FROM parent_childs pc, schema_name.class_generalizations p
								WHERE p.child_id = pc.parent_id
							)
						SELECT
							p.name class_name,
							pc.depth
						FROM
							parent_childs pc join
							schema_name.uml_classes p on p.xmi_id = pc.parent_id
						UNION
						SELECT
							c.name class_name,
							0 as depth
						FROM
							schema_name.uml_classes c
						WHERE
							c.xmi_id = selected_child_id
					) classes
				ORDER by depth desc;
		END
	$$
LANGUAGE 'plpgsql' VOLATILE COST 100;