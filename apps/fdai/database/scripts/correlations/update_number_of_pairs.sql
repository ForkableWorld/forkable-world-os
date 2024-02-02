Create view number_of_pairs as SELECT
	avg(
		`correlations`.`number_of_pairs`
	) AS `number_of_pairs`,
	`correlations`.`cause_variable_id` AS `cause_variable_id`,
	`correlations`.`effect_variable_id` AS `effect_variable_id`
FROM
	`correlations`
GROUP BY
	`correlations`.`cause_variable_id`,
	`correlations`.`effect_variable_id`;

UPDATE global_variable_relationships ac
JOIN number_of_pairs nop on nop.cause_variable_id = ac.cause_variable_id and nop.effect_variable_id = ac.effect_variable_id
set ac.number_of_pairs = nop.number_of_pairs;
