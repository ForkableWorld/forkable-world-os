SELECT
Count(measurements.id) AS numberOfMeasurements,
connectors.`name`
FROM
measurements
INNER JOIN connectors ON connectors.id = measurements.connector_id
GROUP BY
measurements.connector_id
ORDER BY
numberOfMeasurements DESC
LIMIT 100
