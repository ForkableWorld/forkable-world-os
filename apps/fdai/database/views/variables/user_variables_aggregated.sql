CREATE VIEW `user_variables_aggregated` AS 
SELECT
  Max(user_variables.latest_measurement_time) AS latest_measurement_time,
  Max(user_variables.earliest_measurement_time) AS earliest_measurement_time,
  user_variables.variable_id,
  count(*) AS number_of_user_variables,
  variables.name,
  variable_categories.name as variable_category_name,
  units.name as unit_name
FROM
  user_variables
  INNER JOIN variables ON variables.id = user_variables.variable_id
  INNER JOIN units ON units.id = variables.default_unit_id
  INNER JOIN variable_categories ON variable_categories.id = variables.variable_category_id
GROUP BY
  user_variables.variable_id