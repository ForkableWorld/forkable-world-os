SELECT
	`variables`.`name` AS `name`,
	`variables`.`standard_deviation` AS `standard_deviation`,
	`variables`.`variance` AS `variance`,
	`variables`.`minimum_recorded_value` AS `minimum_recorded_value`,
	`variables`.`maximum_recorded_value` AS `maximum_recorded_value`,
	`variables`.`mean` AS `mean`,
	`variables`.`median` AS `median`,
	`variables`.`most_common_unit` AS `most_common_unit`,
	`variables`.`most_common_value` AS `most_common_value`,
	`variables`.`number_of_unique_values` AS `number_of_unique_values`,
	`variables`.`skewness` AS `skewness`,
	`variables`.`kurtosis` AS `kurtosis`
FROM
		`variables`

WHERE
	(
		(
			`variables`.`most_common_value` IS NOT NULL
		)
		AND (
			`variables`.`kurtosis` IS NOT NULL
		)
	)
ORDER BY
	`variables`.`kurtosis` DESC
