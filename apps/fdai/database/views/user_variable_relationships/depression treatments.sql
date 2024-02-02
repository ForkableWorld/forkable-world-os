SELECT
	`correlations`.`id` AS `id`,
	`correlations`.`timestamp` AS `timestamp`,
	`correlations`.`user` AS `user`,
	`correlations`.`correlation` AS `correlation`,
	`correlations`.`vote` AS `vote`,
	`correlations`.`onsetDelay` AS `onsetDelay`,
	`correlations`.`durationOfAction` AS `durationOfAction`,
	`correlations`.`numberOfPairs` AS `numberOfPairs`,
	`correlations`.`value_predicting_high_outcome` AS `value_predicting_high_outcome`,
	`correlations`.`value_predicting_low_outcome` AS `value_predicting_low_outcome`,
	`correlations`.`statisticalSignificance` AS `statisticalSignificance`,
	`correlations`.`causeUnit` AS `causeUnit`,
	`effects`.`name` AS `effectname`,
	`causes`.`name` AS `causename`
FROM
	(
		(
			`correlations`
			JOIN `variables` `effects` ON (
				(
					`effects`.`id` = `correlations`.`effect`
				)
			)
		)
		JOIN `variables` `causes` ON (
			(
				`causes`.`id` = `correlations`.`cause`
			)
		)
	)
WHERE
	(
		(`correlations`.`user` = 2)
		AND (
			`effects`.`name` LIKE '%depression%'
		)
	)