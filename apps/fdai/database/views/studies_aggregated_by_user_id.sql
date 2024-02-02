create or replace view studies_aggregated_by_user_id as
	select `studies`.`user_id`   AS `user_id`,
       count(`studies`.`id`) AS `number_of_studies`
from `studies`
where isnull(`studies`.`deleted_at`)
group by `studies`.`user_id`;

