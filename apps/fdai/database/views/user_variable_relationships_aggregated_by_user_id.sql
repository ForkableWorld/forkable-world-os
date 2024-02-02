create or replace view correlations_aggregated_by_user_id as
	select `correlations`.`user_id`   AS `user_id`,
       count(`correlations`.`id`) AS `number_of_correlations`
from `correlations`
where isnull(`correlations`.`deleted_at`)
group by `correlations`.`user_id`;

