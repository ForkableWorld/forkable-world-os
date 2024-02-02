create or replace view global_variable_relationships_aggregated_by_cause_variable_id as
	select `global_variable_relationships`.`cause_variable_id` AS `cause_variable_id`,
       count(`global_variable_relationships`.`id`)         AS `number_of_global_variable_relationships_as_cause`
from `global_variable_relationships`
where isnull(`global_variable_relationships`.`deleted_at`)
group by `global_variable_relationships`.`cause_variable_id`;

