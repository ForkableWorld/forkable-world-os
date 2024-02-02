SELECT
	`updates`.`user` AS `user`,
	from_unixtime(`updates`.`timestamp`) AS `datetime`,
	`updates`.`numMeasurements` AS `numMeasurements`,
	`updates`.`error` AS `error`,
	`connectors`.`name` AS `name`
FROM
	(
		`updates`
		JOIN `connectors` ON (
			(
				`updates`.`connector` = `connectors`.`id`
			)
		)
	)
ORDER BY
	updates.`timestamp` DESC