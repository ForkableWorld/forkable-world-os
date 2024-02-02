create or replace view tracking_reminders_aggregated_by_user_id as
	select `tracking_reminders`.`user_id`   AS `user_id`,
       count(`tracking_reminders`.`id`) AS `number_of_tracking_reminders`
from `tracking_reminders`
where isnull(`tracking_reminders`.`deleted_at`)
group by `tracking_reminders`.`user_id`;

