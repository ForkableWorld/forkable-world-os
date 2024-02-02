create or replace view user_variables_aggregated_by_user_id as
	select `user_variables`.`user_id` AS                                                      `user_id`,
       count(`user_variables`.`id`) AS                                                    `number_of_user_variables`,
       sum(
               `user_variables`.`number_of_raw_measurements_with_tags_joins_children`) AS `number_of_raw_measurements_with_tags`
from `user_variables`
where isnull(`user_variables`.`deleted_at`)
group by `user_variables`.`user_id`;

