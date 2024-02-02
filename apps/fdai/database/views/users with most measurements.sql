SELECT
user_variables.user_id,
Sum(user_variables.number_of_measurements) AS totalMeasurements,
wp_users.user_login
FROM
user_variables
INNER JOIN wp_users ON wp_users.ID = user_variables.user_id
GROUP BY
user_variables.user_id
ORDER BY
totalMeasurements DESC
LIMIT 100 ;