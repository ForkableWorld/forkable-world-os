create or replace view correlations_aggregated_by_effect_user_variable_id as
	select `correlations`.`effect_user_variable_id` AS `effect_user_variable_id`,
       count(`correlations`.`id`)               AS `number_of_user_correlations_as_effect`
from `correlations`
where isnull(`correlations`.`deleted_at`)
group by `correlations`.`effect_user_variable_id`;

