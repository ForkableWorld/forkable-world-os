SELECT
  measurements.`value`,
  measurements.unit_id,
  `variables`.default_unit_id,
  `variables`.`name`,
  Max(measurements.updated_at) AS latest,
  Count(measurements.id),
  measurements.variable_id,
  measurements.source_name,
  measurements.client_id
FROM
  measurements
  INNER JOIN `variables` ON measurements.variable_id = `variables`.id
WHERE
  measurements.unit_id <> `variables`.default_unit_id
GROUP BY
  `variables`.`name`
ORDER BY
  latest DESC
