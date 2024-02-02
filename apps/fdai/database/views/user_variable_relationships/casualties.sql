SELECT
  `avg forward correlation`.`cause_var` AS `cause_var`,
  `avg forward correlation`.`effect_var` AS `effect_var`,
  (
    `avg forward correlation`.`avgCorrelation` - `avg reverse correlation`.`avgCorrelation`
  ) AS `causality`,
  abs(
      (
        `avg forward correlation`.`avgCorrelation` - `avg reverse correlation`.`avgCorrelation`
      )
  ) AS `absCausality`
FROM
  (
      `avg forward correlation`
      JOIN `avg reverse correlation` ON (
      (
        (
          `avg forward correlation`.`cause_var` = `avg reverse correlation`.`cause_var`
        )
        AND (
          `avg forward correlation`.`effect_var` = `avg reverse correlation`.`effect_var`
        )
        AND (
          `avg forward correlation`.`user` = `avg reverse correlation`.`user`
        )
      )
      )
  )
WHERE
  (
    `avg forward correlation`.`effect_var` LIKE '%mood%'
  )
ORDER BY
  abs(
      (
        `avg forward correlation`.`avgCorrelation` - `avg reverse correlation`.`avgCorrelation`
      )
  ) DESC