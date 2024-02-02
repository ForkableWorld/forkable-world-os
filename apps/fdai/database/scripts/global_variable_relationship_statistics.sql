--Create this view called correlations_statistics

SELECT
	`correlations`.`cause_variable_id` AS `cause_variable_id`,
	`correlations`.`effect_variable_id` AS `effect_variable_id`,
	avg(
		`correlations`.`critical_t_value`
	) AS `critical_t_value`,
	avg(`correlations`.`t_value`) AS `t_value`,
	avg(`correlations`.`p_value`) AS `p_value`,
	avg(
		`correlations`.`confidence_interval`
	) AS `confidence_interval`
FROM
	`correlations`
GROUP BY
	`correlations`.`cause_variable_id`,
	`correlations`.`effect_variable_id`

--  Run this query

UPDATE global_variable_relationships
INNER JOIN correlations_statistics on global_variable_relationships.cause_variable_id = correlations_statistics.cause_variable_id
AND global_variable_relationships.effect_variable_id = correlations_statistics.effect_variable_id
SET
global_variable_relationships.critical_t_value = correlations_statistics.critical_t_value,
global_variable_relationships.t_value = correlations_statistics.t_value,
global_variable_relationships.p_value = correlations_statistics.p_value,
global_variable_relationships.confidence_interval = correlations_statistics.confidence_interval
