SELECT
max(tracker_log.updated_at) as mostRecentRequest,
count(tracker_log.id) AS totalRequests,
wp_users.user_login,
wp_users.updated_at
FROM
tracker_log
INNER JOIN wp_users ON wp_users.ID = tracker_log.user_id
GROUP BY
tracker_log.user_id
ORDER BY
totalRequests DESC
LIMIT 100 ;