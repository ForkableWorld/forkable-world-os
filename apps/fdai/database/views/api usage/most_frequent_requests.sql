SELECT
	Count(0) AS number_of_requests,
	wp_users.user_login,
	tracker_log.method AS method,
	tracker_paths.path AS path,
	tracker_log.created_at AS created_at,
	tracker_log.updated_at AS updated_at,
	tracker_log.client_id AS client_id,
	tracker_log.user_id AS user_id,
	tracker_sessions.client_ip AS client_ip,
	tracker_agents.`name` AS `agent`,
	tracker_agents.browser AS browser
FROM
	(((tracker_log
		JOIN tracker_sessions ON ((tracker_log.session_id = tracker_sessions.id)))
		JOIN tracker_paths ON ((tracker_log.path_id = tracker_paths.id)))
		JOIN tracker_agents ON ((tracker_sessions.agent_id = tracker_agents.id)))
	INNER JOIN wp_users ON tracker_log.user_id = wp_users.ID
WHERE `tracker_log`.`created_at` > NOW() - INTERVAL 1 HOUR
GROUP BY
	`tracker_log`.`path_id`,
	`tracker_sessions`.`client_ip`
ORDER BY
	number_of_requests DESC
LIMIT 1000
