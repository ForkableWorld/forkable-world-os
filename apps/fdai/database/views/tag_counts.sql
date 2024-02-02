create or replace view tag_counts as
	select `t4h`.`tagged_id`                       AS `tagged_id`,
       `t4h`.`tagged`                          AS `tagged`,
       `t4h`.`variable_category_id`            AS `variable_category_id`,
       max(`t4h`.`created_at`)                 AS `created_at`,
       count(`t4h`.`tag`)                      AS `number_of_tags`,
       group_concat(`t4h`.`tag` separator ',') AS `tags`,
       max(`t4h`.`variable_category_id`)       AS `max(t4h.variable_category_id)`,
       max(`t4h`.`client_id`)                  AS `max(t4h.client_id)`
from `tags_4_humans` `t4h`
where ((`t4h`.`number_of_common_tags` > 5) and (`t4h`.`variable_category_id` <> 15))
group by `t4h`.`tagged`
order by `number_of_tags` desc;

