create or replace view correlations_aggregated_by_cause_user_variable_id as
	select `correlations`.`cause_user_variable_id` AS `cause_user_variable_id`,
       count(`correlations`.`id`)              AS `number_of_user_correlations_as_cause`
from `correlations`
where isnull(`correlations`.`deleted_at`)
group by `correlations`.`cause_user_variable_id`;

