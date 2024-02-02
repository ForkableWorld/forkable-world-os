SELECT
measurements.source_id,
Count(measurements.id) AS numberOfMeasurements,
sources.`name`
FROM
measurements
INNER JOIN sources ON sources.id = measurements.source_id
GROUP BY
measurements.source_id
ORDER BY
numberOfMeasurements DESC
LIMIT 100
