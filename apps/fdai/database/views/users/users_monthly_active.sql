SELECT COUNT(DISTINCT user_variables.user_id) as monthlyActiveUsers
FROM
  user_variables
  INNER JOIN wp_users ON user_variables.user_id = wp_users.ID
WHERE
  user_variables.updated_at > NOW() - INTERVAL 1 MONTH AND
  wp_users.user_email NOT LIKE "%test%"