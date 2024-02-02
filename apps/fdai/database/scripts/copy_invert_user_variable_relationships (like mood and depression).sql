INSERT IGNORE INTO correlations
(user, correlation, numberOfPairs, cause, effect)
  SELECT
    `correlations`.`user`,
    -1*(`correlations`.`correlation`) as correlation,
    `correlations`.`numberOfPairs`, cause, 1398
  FROM
    (
        (
            `correlations`
            JOIN `variables` `effects` ON (
            (
              `effects`.`id` = `correlations`.`effect`
            )
            )
          )
        JOIN `variables` `causes` ON (
        (
          `causes`.`id` = `correlations`.`cause`
        )
        )
    )
  WHERE
    (
      (`correlations`.`user` = 2)
      AND (
        `effects`.`name` LIKE '%depression%'
      )
    )