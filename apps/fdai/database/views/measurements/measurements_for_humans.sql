SELECT
	`variables`.`name` AS `variable_name`,
	`measurements`.`value` AS `value`,
	from_unixtime(
		`measurements`.`start_time`
	) AS `start_time`,
	`measurements`.`client_id` AS `client_id`,
	`units`.`name` AS `unitName`,
	`measurements`.`note` AS `note`,
	`sources`.`name` AS `source_ame`,
	`wp_users`.`user_email` AS `user_mail`
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
				JOIN `units` ON (
					(
						`measurements`.`unit_id` = `units`.`id`
					)
				)
			)
			JOIN `sources` ON (
				(
					`measurements`.`source_id` = `sources`.`id`
				)
			)
		)
		JOIN `wp_users` ON (
			(
				`measurements`.`user_id` = `wp_users`.`ID`
			)
		)
	)
ORDER BY
	`measurements`.`start_time` DESC
LIMIT 1000