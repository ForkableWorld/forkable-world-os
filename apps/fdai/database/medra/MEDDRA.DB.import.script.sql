# make sure to medra.zip into database

ALTER TABLE `chemicals`
MODIFY COLUMN `chemical`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ,
ADD INDEX `id` (`chemical`) ;

ALTER TABLE `chemical_aliases`
ADD INDEX `id` (`chemical`) ,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `meddra_all_side_effects`
ADD INDEX `id` (`STITCH compound id flat`) ;

ALTER TABLE `meddra_all_indications`
ADD INDEX `id` (`STITCH compound id flat`) ;

ALTER TABLE `meddra_freq`
ADD INDEX `id` (`STITCH compound id flat`) ;


ALTER TABLE `meddra_freq`
ADD COLUMN `compound_name`  varchar(255) NULL AFTER `side effect name`,
ADD COLUMN `compound_variable_id`  int(10) NULL AFTER `compound_name`,
ADD COLUMN `side_effect_variable_id`  int(10) NULL AFTER `compound_variable_id`;

ALTER TABLE `meddra_all_side_effects`
ADD COLUMN `compound_name`  varchar(255) NULL,
ADD COLUMN `compound_variable_id`  int(10) NULL,
ADD COLUMN `side_effect_variable_id`  int(10) NULL;

ALTER TABLE `meddra_all_indications`
ADD COLUMN `compound_name`  varchar(255) NULL,
ADD COLUMN `compound_variable_id`  int(10) NULL,
ADD COLUMN `condition_variable_id`  int(10) NULL;

# Add compound names

update `meddra_all_indications`
INNER JOIN `chemicals` ON meddra_all_indications.`STITCH compound id flat` = chemicals.chemical
set meddra_all_indications.compound_name = chemicals.`name`

update `meddra_all_side_effects`
INNER JOIN `chemicals` ON meddra_all_side_effects.`STITCH compound id flat` = chemicals.chemical
set meddra_all_side_effects.compound_name = chemicals.`name`

update `meddra_freq`
INNER JOIN `chemicals` ON meddra_freq.`STITCH compound id flat` = chemicals.chemical
set meddra_freq.compound_name = chemicals.`name`


# Add treatment group variables
# variableCategory = Treatments (13), defaultUnit = mg (7)
insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public,description)
select compound_name,13,7,'SUM',1,`STITCH compound id flat` from meddra_freq;

insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public,description)
select compound_name,13,7,'SUM',1,`STITCH compound id flat` from meddra_all_side_effects;

insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public,description)
select compound_name,13,7,'SUM',1,`STITCH compound id flat` from meddra_all_indications;

insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public,description)
select compound_name,13,7,'SUM',1,`STITCH compound id flat` from meddra_all_indications;


# Add symptom group variables
# variableCategory = Symptoms (10), defaultUnit = /5 (10)
insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public)
select `concept name`,10,10,'MEAN',1 from meddra_all_indications;

insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public)
select `side effect name`,10,10,'MEAN',1 from meddra_all_side_effects;

insert ignore into variables(name,`variable_category_id`,`default_unit_id`,`combination_operation`,public)
select `side effect name`,10,10,'MEAN',1 from meddra_freq;


# Add QM compound variable ids to meddra tables
update meddra_freq
set meddra_freq.compound_variable_id =
(select v.id
from variables v
where v.name = meddra_freq.compound_name);

update meddra_all_side_effects
set meddra_all_side_effects.compound_variable_id =
(select v.id
from variables v
where v.name = meddra_all_side_effects.compound_name);

update meddra_all_indications
set meddra_all_indications.compound_variable_id =
(select v.id
from variables v
where v.name = meddra_all_indications.compound_name);

# Add QM symptom variable ids to meddra tables
update meddra_all_indications
set meddra_all_indications.condition_variable_id =
(select v.id
from variables v
where v.name = meddra_all_indications.`concept name`);

update meddra_all_side_effects
set meddra_all_side_effects.side_effect_variable_id =
(select v.id
from variables v
where v.name = meddra_all_side_effects.`side effect name`);

update meddra_freq
set meddra_freq.side_effect_variable_id =
(select v.id
from variables v
where v.name = meddra_freq.`side effect name`);

# insert correlations for side effects
insert ignore into correlations
(user_id, forward_pearson_correlation_coefficient, cause_variable_id, effect_variable_id, onset_delay, duration_of_action)
select
3,
(AVG(meddra_freq.`a lower bound on the frequency`)+ AVG(meddra_freq.`an upper bound on the frequency`)/2),
meddra_freq.compound_variable_id,
meddra_freq.side_effect_variable_id,
0,
86400
from meddra_freq
GROUP BY meddra_freq.compound_variable_id, meddra_freq.side_effect_variable_id;

insert ignore into correlations
(user_id, forward_pearson_correlation_coefficient, cause_variable_id, effect_variable_id, onset_delay, duration_of_action)
select
3,
-0.5,
meddra_all_indications.compound_variable_id,
meddra_all_indications.side_effect_variable_id,
0,
86400
from meddra_all_indications
GROUP BY meddra_all_indications.compound_variable_id, meddra_all_indications.condition_variable_id;


INSERT INTO global_variable_relationships (
  global_variable_relationships.correlation,
onset_delay,
duration_of_action,
global_variable_relationships.cause_variable_id,
global_variable_relationships.effect_variable_id,
global_variable_relationships.number_of_pairs,
global_variable_relationships.optimal_pearson_product,
global_variable_relationships.number_of_users,
global_variable_relationships.number_of_correlations,
global_variable_relationships.statistical_significance,
global_variable_relationships.aggregate_qm_score,
global_variable_relationships.reverse_pearson_correlation_coefficient,
global_variable_relationships.predictive_pearson_correlation_coefficient,
global_variable_relationships.`status`
)
SELECT
  correlations.forward_pearson_correlation_coefficient,
  0,
  86400,
  correlations.cause_variable_id,
  correlations.effect_variable_id,
  correlations.number_of_pairs,
  ABS(correlations.forward_pearson_correlation_coefficient),
  correlations.number_of_pairs,
  correlations.number_of_pairs,
  1-EXP(-correlations.number_of_pairs/100),
  ABS(correlations.forward_pearson_correlation_coefficient) * (1-EXP(-correlations.number_of_pairs/100)),
  0,
  correlations.forward_pearson_correlation_coefficient,
  'MedDRA'
FROM
  correlations
WHERE correlations.user_id = 3
