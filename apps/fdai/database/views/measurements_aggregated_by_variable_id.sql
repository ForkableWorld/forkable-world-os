create or replace view measurements_aggregated_by_variable_id as
	select `measurements`.`variable_id`     AS `variable_id`,
       min(`measurements`.`start_time`) AS `earliest_non_tagged_measurement_time`,
       max(`measurements`.`start_time`) AS `latest_non_tagged_measurement_time`,
       max(`measurements`.`value`)      AS `maximum_recorded_value`,
       avg(`measurements`.`value`)      AS `mean`,
       min(`measurements`.`value`)      AS `minimum_recorded_value`,
       count(`measurements`.`id`)       AS `number_of_raw_measurements`,
       max(`measurements`.`updated_at`) AS `newest_data_at`
from `measurements`
where isnull(`measurements`.`deleted_at`)
group by `measurements`.`variable_id`;

