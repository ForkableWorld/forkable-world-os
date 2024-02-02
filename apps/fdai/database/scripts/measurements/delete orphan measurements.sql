SELECT count(id) AS numberOfMeasurements,
 variable_id
 FROM measurements
WHERE variable_id not in (SELECT id FROM variables)
GROUP BY variable_id;

DELETE measurements FROM measurements
WHERE variable_id not in (SELECT id FROM variables);

DELETE user_variables FROM user_variables
WHERE variable_id not in (SELECT id FROM variables);