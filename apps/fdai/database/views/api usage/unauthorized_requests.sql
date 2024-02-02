SELECT
	`tracker_log`.`id` AS `id`,
	`tracker_log`.`session_id` AS `session_id`,
	`tracker_log`.`path_id` AS `path_id`,
	`tracker_log`.`query_id` AS `query_id`,
	`tracker_log`.`method` AS `method`,
	`tracker_log`.`route_path_id` AS `route_path_id`,
	`tracker_log`.`is_ajax` AS `is_ajax`,
	`tracker_log`.`is_secure` AS `is_secure`,
	`tracker_log`.`is_json` AS `is_json`,
	`tracker_log`.`wants_json` AS `wants_json`,
	`tracker_log`.`error_id` AS `error_id`,
	`tracker_log`.`created_at` AS `created_at`,
	`tracker_log`.`updated_at` AS `updated_at`,
	`tracker_log`.`client_id` AS `client_id`,
	`tracker_log`.`user_id` AS `user_id`,
	count(0) AS `number_of_requests`,
	`tracker_sessions`.`client_ip` AS `client_ip`,
	`tracker_paths`.`path` AS `path`,
	`tracker_agents`.`name` AS `name`,
	`tracker_agents`.`browser` AS `browser`
FROM
	(
		(
			(
				`tracker_log`
				JOIN `tracker_sessions` ON (
					(
						`tracker_log`.`session_id` = `tracker_sessions`.`id`
					)
				)
			)
			JOIN `tracker_paths` ON (
				(
					`tracker_log`.`path_id` = `tracker_paths`.`id`
				)
			)
		)
		JOIN `tracker_agents` ON (
			(
				`tracker_sessions`.`agent_id` = `tracker_agents`.`id`
			)
		)
	)
WHERE
	isnull(`tracker_log`.`user_id`)
GROUP BY
	`tracker_log`.`path_id`,
	`tracker_sessions`.`client_ip`