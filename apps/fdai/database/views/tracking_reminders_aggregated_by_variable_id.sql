create or replace view tracking_reminders_aggregated_by_variable_id as
	select `tracking_reminders`.`variable_id` AS `variable_id`,
       count(`tracking_reminders`.`id`)   AS `number_of_tracking_reminders`
from `tracking_reminders`
where isnull(`tracking_reminders`.`deleted_at`)
group by `tracking_reminders`.`variable_id`;

