create or replace view tags_4_humans as
	select `tagged`.`id`                    AS `tagged_id`,
       `ct`.`created_at`                AS `tag_created`,
       `tagged`.`name`                  AS `tagged`,
       `tag`.`name`                     AS `tag`,
       `tagged`.`variable_category_id`  AS `variable_category_id`,
       `tagged`.`number_of_common_tags` AS `number_of_common_tags`,
       `tagged`.`created_at`            AS `created_at`,
       `tagged`.`client_id`             AS `client_id`,
       `tagged`.`brand_name`            AS `brand_name`
from ((`common_tags` `ct` join `variables` `tagged` on ((`ct`.`tagged_variable_id` = `tagged`.`id`)))
         join `variables` `tag` on ((`ct`.`tag_variable_id` = `tag`.`id`)))
where ((`tagged`.`number_of_common_tags` > 5) and (`tagged`.`variable_category_id` <> 15))
order by `tagged`.`number_of_common_tags` desc;

