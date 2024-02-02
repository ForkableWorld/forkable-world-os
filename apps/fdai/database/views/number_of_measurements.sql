create or replace view number_of_measurements as
	select `m`.`variable_id`                AS `variable`,
       count(0)                         AS `measurements`,
       `v`.`name`                       AS `variable_name`,
       `v`.`number_of_raw_measurements` AS `from_variables`
from (`measurements` `m`
         join `variables` `v` on ((`v`.`id` = `m`.`variable_id`)))
group by `m`.`variable_id`
order by `measurements` desc;

