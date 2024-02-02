CREATE VIEW `number_of_common_tags` AS
SELECT count(common_tags.tag_variable_id) as total, variables.id
FROM variables
right join common_tags on variables.id = common_tags.tagged_variable_id
group by common_tags.tagged_variable_id
order by total DESC
LIMIT 100