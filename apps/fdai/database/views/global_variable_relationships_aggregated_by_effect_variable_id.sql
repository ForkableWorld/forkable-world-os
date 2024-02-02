create or replace view global_variable_relationships_aggregated_by_effect_variable_id as
	select `global_variable_relationships`.`effect_variable_id` AS `effect_variable_id`,
       count(`global_variable_relationships`.`id`)          AS `number_of_global_variable_relationships_as_effect`
from `global_variable_relationships`
where isnull(`global_variable_relationships`.`deleted_at`)
group by `global_variable_relationships`.`effect_variable_id`;

