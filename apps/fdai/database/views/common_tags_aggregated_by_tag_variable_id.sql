create or replace view common_tags_aggregated_by_tag_variable_id as
	select `common_tags`.`tag_variable_id`           AS `tag_variable_id`,
       count(`common_tags`.`tagged_variable_id`) AS `number_common_tagged_by`
from `common_tags`
where isnull(`common_tags`.`deleted_at`)
group by `common_tags`.`tag_variable_id`;

