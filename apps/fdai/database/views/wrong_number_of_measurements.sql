create or replace view wrong_number_of_measurements as
	select `number_of_measurements`.`variable`       AS `variable`,
       `number_of_measurements`.`measurements`   AS `measurements`,
       `number_of_measurements`.`variable_name`  AS `variable_name`,
       `number_of_measurements`.`from_variables` AS `from_variables`
from `number_of_measurements`
where (`number_of_measurements`.`measurements` <> `number_of_measurements`.`from_variables`);

