SELECT
  `variables`.`id` AS `id`,
  `variables`.`name` AS `name`,
  `variables`.`combination_operation` AS `combination_operation`,
  `variables`.`filling_value` AS `filling_value`,
  `variables`.`maximum_allowed_value` AS `maximum_value`,
  `variables`.`minimum_allowed_value` AS `minimum_value`,
  `variables`.`onset_delay` AS `onset_delay`,
  (
    `variables`.`duration_of_action` / 3600
  ) AS `duration_of_action_hours`,
  `variables`.`updated_at` AS `updated`,
  `variables`.`is_public` AS `is_public`,
  `variables`.`cause_only` AS `cause_only`,
  `units`.`name` AS `unit`,
  `variable_categories`.`name` AS `category`,
  `vars`.`name` AS `parent_var`
FROM
  (
      (
          (
              `variables`
              LEFT JOIN `units` ON (
              (
                `units`.`id` = `variables`.`default_unit_id`
              )
              )
            )
          LEFT JOIN `variable_categories` ON (
          (
            `variables`.`variable_category_id` = `variable_categories`.`id`
          )
          )
        )
      LEFT JOIN `variables` `vars` ON (
      (
        `vars`.`id` = `variables`.`parent_id`
      )
      )
  )