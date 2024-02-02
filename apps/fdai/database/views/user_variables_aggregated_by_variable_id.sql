create or replace view user_variables_aggregated_by_variable_id as
	select `user_variables`.`variable_id` AS `variable_id`,
       count(`user_variables`.`id`)   AS `number_of_user_variables`
from `user_variables`
where isnull(`user_variables`.`deleted_at`)
group by `user_variables`.`variable_id`;

