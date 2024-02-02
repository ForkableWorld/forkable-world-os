INSERT IGNORE user_variables (
  `user`,
  variable,
  unit,
  last_source,
  first_measurement_time,
  last_measurement_time,
  number_of_measurements,
  measurements_at_last_analysis,
  last_unit,
  last_value
) SELECT
    `user`,
    variable,
    unit,
    last_source,
    first_measurement_time,
    last_measurement_time,
    number_of_measurements,
    measurements_at_last_analysis,
    last_unit,
    last_value
  FROM
    (
      SELECT
        `user`,
        variable,
        unit,
        source AS last_source,
        MIN(TIMESTAMP) AS first_measurement_time,
        MAX(TIMESTAMP) AS last_measurement_time,
        count(id) AS number_of_measurements,
        0 AS measurements_at_last_analysis,
        unit AS last_unit,
        `value`	AS last_value
      FROM
        measurements
      GROUP BY
        variable,
        USER
    ) m ON DUPLICATE KEY UPDATE last_source = m.last_source,
  number_of_measurements = m.number_of_measurements,
  last_measurement_time = m.last_measurement_time,
  last_unit = m.last_unit,
  last_value = m.last_value,
  last_measurement_time = m.last_measurement_time