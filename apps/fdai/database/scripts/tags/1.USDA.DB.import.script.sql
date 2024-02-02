# make sure to import usda-nutrient-database.zip into database

# run this on table nutr_def
ALTER TABLE  `nutr_def` ADD  `qm_nutrient_id` INT( 11 ) NOT NULL ;
ALTER TABLE  `fd_group` ADD  `qm_food_category_id` INT( 11 ) NOT NULL ;
ALTER TABLE  `food_des` ADD  `qm_food_id` INT( 11 ) NOT NULL ;
ALTER TABLE  `food_des` CHANGE  `Long_Desc` varchar( 125 ) NOT NULL ;

# nutrition food db
# Add food group variables
# variableCategory = Foods (15), defaultUnit = g (6)
insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public)
select FdGrp_Desc,15,6,'SUM',1 from fd_group;

# Update existing variable settings
update variables
JOIN fd_group ON fd_group.FdGrp_Desc = `variables`.`name`
set variable_category_id = 15,
variables.default_unit_id = 6,
variables.combination_operation = 'SUM',
public = 1;

# Add food group variable id's to food group table
update fd_group fg
set fg.qm_food_category_id
= (select v.id from variables v where v.name = fg.FdGrp_Desc);

# Add nutrient variables
# Fix micrograms
UPDATE nutr_def
set `Units` = 'mcg' where `Units`= '?g';

# Fix Fatty Acids
UPDATE nutr_def
set `NutrDesc` = CONCAT(`NutrDesc`, ' saturated fatty acids')
where `NutrDesc` LIKE '%:0%';

# Fix monounsaturated Fatty Acids
UPDATE nutr_def
set `NutrDesc` = CONCAT(`NutrDesc`, ' monounsaturated fatty acids')
where `NutrDesc` LIKE '%:1%';

# Fix polyunsaturated Fatty Acids
UPDATE nutr_def
set `NutrDesc` = CONCAT(`NutrDesc`, ' polyunsaturated fatty acids')
where `NutrDesc` LIKE '%:2%' OR
`NutrDesc` LIKE '%:3%' OR
`NutrDesc` LIKE '%:4%' OR
`NutrDesc` LIKE '%:5%' OR
`NutrDesc` LIKE '%:6%';

# Add unit_id column to nutrients table
ALTER TABLE nutr_def
  ADD COLUMN qm_unit_id int DEFAULT null;

# Add unit_id's to nutrients table
update nutr_def
INNER JOIN units ON nutr_def.Units = units.abbreviated_name
set qm_unit_id = units.id;

# Convert Vitamin A from UI to mcg
update nut_data
set `Nutr_Val` = 0.45 * `Nutr_Val`
where `Nutr_No` = 318;

update nutr_def
set `NutrDesc` = 'Vitamin A (mcg)', qm_unit_id = 32
where `NutrDesc` = 'Vitamin A, IU';

# Convert Vitamin D from UI to mcg
update nut_data
set `Nutr_Val` = 0.025 * `Nutr_Val`
where `Nutr_No` = 324;

update nutr_def
set `NutrDesc` = 'Vitamin D (mcg)', qm_unit_id = 32
where `NutrDesc` = 'Vitamin D';

# Convert Energy from Joules to kcal
update IGNORE nut_data
set `Nutr_Val` = 0.23900573614 * `Nutr_Val`, `Nutr_No` = 208
where `Nutr_No` = 268;

# Delete duplicate energy records
delete nut_data from nut_data
where `Nutr_No` = 268;

# Add nutrients to variables table
# variableCategory = Nutrients (11)
insert ignore into
variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`, public)
select NutrDesc, 11, qm_unit_id, 'SUM', 1 from nutr_def;

# Update nutrients in variables table
update variables
JOIN nutr_def ON nutr_def.NutrDesc = `variables`.`name`
set variable_category_id = 15,
variables.default_unit_id = nutr_def.qm_unit_id,
variables.combination_operation = 'SUM',
public =1;

# Add nutrient variable id's to nutrients table
update nutr_def n
set n.qm_nutrient_id = (select v.id from variables v where v.name = n.NutrDesc);

# Add parent_id for categories to foods table
ALTER TABLE food_des
  ADD COLUMN qm_parent_id int DEFAULT null;

# Add food category parent id's to foods table
update food_des n
set n.qm_parent_id = (select fd_group.qm_food_category_id from fd_group where fd_group.FdGrp_Cd = n.FdGrp_Cd);

# Add food variables
# variable_category = Foods (15), unit_id = 6 for grams
insert ignore into variables
(parent_id, name,`variable_category_id`,`default_unit_id`,`combination_operation`, public)
select
qm_parent_id, Long_Desc,15,6,'SUM',1
from food_des fd ;

# Convert measurements from servings to grams
update measurements
INNER JOIN food_des ON food_des.qm_food_id = measurements.variable_id
set unit_id = 6
where unit_id = 44 and value > 5;

# Convert measurements from servings to grams
update measurements
INNER JOIN food_des ON food_des.qm_food_id = measurements.variable_id
set unit_id = 6, measurements.value = measurements.value * 100
where unit_id = 44 and value < 5;

# Add food varible id's to foods table
update food_des f
set f.qm_food_id =
(select v.id
from variables v
where v.name = f.Long_Desc);