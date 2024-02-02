insert ignore into common_tags(
tagged_variable_id,
tag_variable_id,
number_of_data_points,
standard_error,
tag_variable_unit_id,
tagged_variable_unit_id,
conversion_factor,
client_id,
created_at,
updated_at
)
select
    tagged_variable_id,
    tag_variable_id,
    number_of_data_points,
    standard_error,
    tag_variable_unit_id,
    tagged_variable_unit_id,
    conversion_factor,
    'USDA_IMPORT',
    NOW(),
    NOW()
from common_tags_view;