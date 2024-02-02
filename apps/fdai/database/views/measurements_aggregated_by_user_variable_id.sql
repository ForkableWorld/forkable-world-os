create or replace view measurements_aggregated_by_user_variable_id as
	select `measurements`.`user_variable_id` AS `user_variable_id`,
       max(`measurements`.`updated_at`)  AS `newest_data_at`
from `measurements`
where isnull(`measurements`.`deleted_at`)
group by `measurements`.`user_variable_id`;

