create or replace view missing_measurements as
	select count(0) AS `num`
from (`demo_measurements` `d`
         left join `measurements` `m`
                   on (((`d`.`variable_id` = `m`.`variable_id`) and (`d`.`start_time` = `m`.`start_time`))))
where isnull(`m`.`id`);

