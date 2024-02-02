# Create a view with this script.  Then insert all items from this view in the tags table. 

SELECT
    `food_des`.`qm_food_id` AS `tagged_variable_id`,
    `nutr_def`.`qm_nutrient_id` AS `tag_variable_id`,
    (
        `nut_data`.`Nutr_Val` / 100
    ) AS `conversion_factor`,
    `nut_data`.`Num_Data_Pts` AS `number_of_data_points`,
    `nut_data`.`Std_Error` AS `standard_error`,
    `units`.`id` AS `tag_variable_unit_id`,
    6 AS `tagged_variable_unit_id`
FROM
    (
        (
            (
                `food_des`
                JOIN `nut_data` ON (
                    (
                        `food_des`.`NDB_No` = `nut_data`.`NDB_No`
                    )
                )
            )
            JOIN `nutr_def` ON (
                (
                    `nutr_def`.`Nutr_No` = `nut_data`.`Nutr_No`
                )
            )
        )
        JOIN `units` ON (
            (
                (
                    `units`.`abbreviated_name` = CONVERT (
                        `nutr_def`.`Units` USING utf8
                    )
                )
                AND (
                    CONVERT (
                        `nutr_def`.`Units` USING utf8
                    ) = `units`.`abbreviated_name`
                )
            )
        )
    )