select Count(v.id) as numberOfVariables, vc.name, vc.id
from variables v
join variable_categories vc on vc.id = v.variable_category_id
group by v.variable_category_id