SELECT
	count(`correlations`.`id`) AS `number_of_user_correlations_as_effect`,
	`user_variables`.`user_id` AS `user_id`,
	`user_variables`.`variable_id` AS `variable_id`
FROM
	(
		`user_variables`
		JOIN `correlations` ON (
			(
				(
					`correlations`.`effect_variable_id` = `user_variables`.`variable_id`
				)
				AND (
					`correlations`.`user_id` = `user_variables`.`user_id`
				)
			)
		)
	)
GROUP BY
	`correlations`.`effect_variable_id`,
	`correlations`.`user_id`



