SELECT
	`variables`.`name` AS `name`,
	`measurements`.`value` AS `value`,
	`units`.`name` AS `unitName`,
	`measurements`.`original_value` AS `original_value`,
	`originalUnits`.`name` AS `originalUnit`,
	from_unixtime(
		`measurements`.`start_time`
	) AS `timestamp`,
	`measurements`.`variable_id` AS `variable_id`,
	`variable_categories`.`name` AS `variableCategory`,
	`variable_categories`.`maximum_allowed_value` AS `maximum_allowed_value`,
	`variable_categories`.`minimum_allowed_value` AS `minimum_allowed_value`,
	`units`.`minimum_value` AS `minimum_value`,
	`units`.`maximum_value` AS `maximum_value`,
	`variables`.`maximum_allowed_value` AS `varMax`,
	`variables`.`minimum_allowed_value` AS `varMin`
FROM
	(
		(
			(
				(
					`measurements`
					JOIN `variables` ON (
						(
							`measurements`.`variable_id` = `variables`.`id`
						)
					)
				)
				JOIN `variable_categories` ON (
					(
						`measurements`.`variable_category_id` = `variable_categories`.`id`
					)
				)
			)
			JOIN `units` ON (
				(
					`measurements`.`unit_id` = `units`.`id`
				)
			)
		)
		JOIN `units` `originalUnits` ON (
			(
				`originalUnits`.`id` = `measurements`.`original_unit_id`
			)
		)
	)
WHERE
	(
		(
			(
				`variables`.`maximum_allowed_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` > `variables`.`maximum_allowed_value`
			)
		)
		OR (
			(
				`units`.`maximum_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` > `units`.`maximum_value`
			)
		)
		OR (
			(
				`variable_categories`.`maximum_allowed_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` > `variable_categories`.`maximum_allowed_value`
			)
		)
		OR (
			(
				`variables`.`minimum_allowed_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` < `variables`.`minimum_allowed_value`
			)
		)
		OR (
			(
				`units`.`minimum_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` < `units`.`minimum_value`
			)
		)
		OR (
			(
				`variable_categories`.`minimum_allowed_value` IS NOT NULL
			)
			AND (
				`measurements`.`value` < `variable_categories`.`minimum_allowed_value`
			)
		)
	)
ORDER BY
	`measurements`.`start_time`
LIMIT 1000