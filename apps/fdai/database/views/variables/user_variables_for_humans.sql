SELECT
  `variables`.`name` AS `original_name`,
  `user_variables`.`variable_id` AS `variable_id`,
  `user_variables`.`user_id` AS `user`,
  `user_variables`.`minimum_allowed_value` AS `minimum_value`,
  `user_variables`.`maximum_allowed_value` AS `maximum_value`,
  `user_variables`.`filling_value` AS `filling_value`,
  `user_variables`.`onset_delay` AS `onset_delay`,
  `user_variables`.`duration_of_action` AS `duration_of_action`,
  `user_variables`.`variable_category_id` AS `variable_category`,
  `user_variables`.`updated_at` as updatedAt,
  `user_variables`.`created_at`,
  `user_variables`.`client_id`,
  `user_variables`.`is_public` AS `is_public`,
  `user_variables`.`cause_only` AS `cause_only`,
  `user_variables`.`filling_type` AS `filling_type`,
  `units`.`name` AS `unit`,
  `joined_vars`.`name` AS `joined_to`,
  `parents`.`name` AS `parent`
FROM
  (
      (
          (
              (
                  `user_variables`
                  LEFT JOIN `variables` ON (
                  (
                    `user_variables`.`variable_id` = `variables`.`id`
                  )
                  )
                )
              LEFT JOIN `units` ON (
              (
                `variables`.`default_unit_id` = `units`.`id`
              )
              )
            )
          LEFT JOIN `variables` `joined_vars` ON (
          (
            `joined_vars`.`id` = `user_variables`.`join_with`
          )
          )
        )
      LEFT JOIN `variables` `parents` ON (
      (
        `user_variables`.`parent_id` = `parents`.`id`
      )
      )
  )
WHERE user_variables.status = "0"
GROUP BY user_variables.client_id
ORDER BY
  updatedAt DESC
LIMIT 1000