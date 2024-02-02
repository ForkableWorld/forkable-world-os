DELETE
correlations
FROM
correlations
INNER JOIN `variables` AS cvars ON cvars.id = correlations.cause_variable_id
INNER JOIN `variables` AS evars ON correlations.effect_variable_id = evars.id
WHERE
evars.variable_category_id = cvars.variable_category_id;

DELETE
global_variable_relationships
FROM
global_variable_relationships
INNER JOIN `variables` AS cvars ON cvars.id = global_variable_relationships.cause_variable_id
INNER JOIN `variables` AS evars ON global_variable_relationships.effect_variable_id = evars.id
WHERE
evars.variable_category_id = cvars.variable_category_id;
