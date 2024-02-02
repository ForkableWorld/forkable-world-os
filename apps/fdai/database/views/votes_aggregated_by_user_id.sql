create or replace view votes_aggregated_by_user_id as
	select `votes`.`user_id` AS `user_id`, count(`votes`.`id`) AS `number_of_votes`
from `votes`
where isnull(`votes`.`deleted_at`)
group by `votes`.`user_id`;

