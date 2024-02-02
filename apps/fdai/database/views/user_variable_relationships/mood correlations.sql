SELECT
	`causes`.`name` AS `cause_var`,
	`effects`.`name` AS `effect_var`,
	`correlations`.`correlation` AS `correlation`,
	round(
		(
			`correlations`.`durationOfAction` / 86400
		),
		0
	) AS `durationOfAction`,
	abs(
		`correlations`.`correlation`
	) AS `absoluteCorrelation`,
	round(
		(
			`correlations`.`onsetDelay` / 86400
		),
		0
	) AS `onsetDelay`,
	`correlations`.`value_predicting_high_outcome` AS `value_predicting_high_outcome`,
	`correlations`.`value_predicting_low_outcome` AS `value_predicting_low_outcome`,
	`correlations`.`causeUnit` AS `causeUnit`,
	`correlations`.`user` AS `user`,
	`correlations`.`numberOfPairs` AS `numberOfPairs`,
	from_unixtime(`correlations`.`timestamp`) AS `calculatedTimestamp`,
	`effects`.`id` AS `effectid`,
	`causes`.`id` AS `causeid`
FROM
	(
		(
			`correlations`
			JOIN `variables` `causes` ON (
				(
					`correlations`.`cause` = `causes`.`id`
				)
			)
		)
		JOIN `variables` `effects` ON (
			(
				`correlations`.`effect` = `effects`.`id`
			)
		)
	)
WHERE
	(
		`effects`.`name` LIKE '%mood%'
	)
ORDER BY
	from_unixtime(`correlations`.`timestamp`) DESC