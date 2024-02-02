SELECT
	count(
		`global_variable_relationships`.`id`
	) AS `number_of_global_variable_relationships_as_effect`,
	`variables`.`id` AS `id`
FROM
	(
		`variables`
		JOIN `global_variable_relationships` ON (
			(
				`global_variable_relationships`.`effect_variable_id` = `variables`.`id`
			)
		)
	)
GROUP BY
	`variables`.`id`
LIMIT 1000
