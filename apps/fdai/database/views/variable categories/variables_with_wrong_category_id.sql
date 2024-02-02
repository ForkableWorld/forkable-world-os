SELECT
 id,
 variable_
user_id,
created_at
 FROM variables
WHERE variable_id not in (SELECT id FROM variables)
GROUP BY variable_id;