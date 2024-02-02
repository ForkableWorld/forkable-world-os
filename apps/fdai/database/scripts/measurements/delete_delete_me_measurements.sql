SELECT v.name, m.user_id, COUNT(m.id), wu.user_login as numberOfMeasurements
from measurements as m
join variables as v on v.id = m.variable_id
join wp_users as wu on wu.ID = m.user_id
where m.deleted_at IS NOT NULL
GROUP BY m.variable_id AND m.user_id
order by numberOfMeasurements desc ;

SELECT v.name, m.user_id, COUNT(m.id), wu.user_login as numberOfMeasurements
from measurements as m
join variables as v on v.id = m.variable_id
join wp_users as wu on wu.ID = m.user_id
where m.error = 'delete_me'
GROUP BY m.variable_id AND m.user_id
order by numberOfMeasurements desc ;

DELETE measurements from measurements
where error='delete_me';

SELECT v.name, m.user_id,
COUNT(m.id),
wu.user_login as numberOfMeasurements
from user_variables as uv
join measurements as m on m.user_id = uv.user_id AND m.variable_id = uv.variable_id
join variables as v on v.id = m.variable_id
join wp_users as wu on wu.ID = m.user_id
where m.error = 'delete_me'
GROUP BY m.variable_id AND m.user_id
order by numberOfMeasurements desc ;