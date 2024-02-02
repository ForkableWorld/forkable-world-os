create or replace view connections_aggregated_by_user_id as
	select `connections`.`user_id`   AS `user_id`,
       count(`connections`.`id`) AS `number_of_connections`
from `connections`
where isnull(`connections`.`deleted_at`)
group by `connections`.`user_id`;

