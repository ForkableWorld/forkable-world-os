delete
	`global_variable_relationships`
FROM
	(
		(
			`global_variable_relationships`
			JOIN `variables` ON (
				(
					`global_variable_relationships`.`effect_variable_id` = `variables`.`id`
				)
			)
		)
		JOIN `variable_categories` ON (
			(
				`variables`.`variable_category_id` = `variable_categories`.`id`
			)
		)
	)
WHERE
	(
		`variable_categories`.`outcome` <> 1
	)
LIMIT 1000;

delete
	`correlations`
FROM
	(
		(
			`correlations`
			JOIN `variables` ON (
				(
					`correlations`.`effect_variable_id` = `variables`.`id`
				)
			)
		)
		JOIN `variable_categories` ON (
			(
				`variables`.`variable_category_id` = `variable_categories`.`id`
			)
		)
	)
WHERE
	(
		`variable_categories`.`outcome` <> 1
	)
LIMIT 1000;

UPDATE
variables
INNER JOIN `variables` ON number_of_global_variable_relationships_as_effect.id = `variables`.id
set variables.number_of_global_variable_relationships_as_effect = number_of_global_variable_relationships_as_effect.number_of_global_variable_relationships_as_effect;


UPDATE
		`user_variables`
		JOIN `number_of_user_correlations_as_effect` ON (
			(
				(
					`number_of_user_correlations_as_effect`.`variable_id` = `user_variables`.`variable_id`
				)
				AND (
					`number_of_user_correlations_as_effect`.`user_id` = `user_variables`.`user_id`
				)
			)
		)
	)
set user_variables.number_of_user_correlations_as_effect = number_of_user_correlations_as_effect.number_of_user_correlations_as_effect;
