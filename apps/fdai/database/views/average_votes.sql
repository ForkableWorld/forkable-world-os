create or replace view average_votes as
	select count(`v`.`id`)          AS `number_of_votes`,
       avg(`v`.`value`)         AS `average_vote`,
       `v`.`cause_variable_id`  AS `cause_variable_id`,
       `v`.`effect_variable_id` AS `effect_variable_id`
from `votes` `v`
group by `v`.`cause_variable_id`, `v`.`effect_variable_id`;

