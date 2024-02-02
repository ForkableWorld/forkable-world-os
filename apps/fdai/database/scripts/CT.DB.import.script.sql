
-- first import CT tables into qm database
-- tables list which should be imported:
-- ct_conditions,  ct_sideeffects,  ct_causes,  ct_symptoms,  ct_treatments,
-- intuitive_condition_cause_votes,  ct_condition_symptom,  ct_condition_treatment,  ct_treatment_sideeffect


-- insert new category
INSERT INTO `variable_categories` (`id`, `name`, `filling-value`, `maximum-value`, `minimum-value`, `duration-of-action`, `onset-delay`, `combination-operation`, `updated`, `cause-only`, `is_public`, `filling-type`) VALUES ('18', 'Causes of Illness', '-1', NULL, NULL, '86400', '0', '1', '1', '0', '1', '');

ALTER TABLE `ct_conditions` ADD `varID` INT(11) NOT NULL AFTER `conname`;
ALTER TABLE `ct_sideeffects` ADD `varID` INT(11) NOT NULL AFTER `seName`;
ALTER TABLE `ct_causes` ADD `varID` INT(11) NOT NULL AFTER `causeName`;
ALTER TABLE `ct_symptoms` ADD `varID` INT(11) NOT NULL AFTER `symName`;
ALTER TABLE `ct_treatments` ADD `varID` INT(11) NOT NULL AFTER `treName`;


-- conditions
insert ignore into variables(name,`variable-category`,`default-unit`,`combination-operation`)
select conname,16,21,1 from ct_conditions;

update ct_conditions c set c.varID = (select id from variables where name = c.conname);


-- side-effect
insert ignore into variables(name,`variable-category`,`default-unit`,`combination-operation`)
select sename,10,21,1 from ct_sideeffects;

update ct_sideeffects s set s.varID = (select id from variables where name = s.sename);


-- symptoms
insert ignore into variables(name,`variable-category`,`default-unit`,`combination-operation`)
select symname,10,21,1 from ct_symptoms;

update ct_symptoms s set s.varID = (select id from variables where name = s.symName);


-- treatments
insert ignore into variables(name,`variable-category`,`default-unit`,`combination-operation`)
select trename,13,21,1 from ct_treatments;

update ct_treatments t set t.varID = (select id from variables where name = t.treName);


-- causes
insert ignore into variables(name,`variable-category`,`default-unit`,`combination-operation`)
select causename,18,21,1 from ct_causes;

update ct_causes c set c.varID = (select id from variables where name = c.causename);


DELETE FROM  `ct_condition_treatment` WHERE  `majorImprove` =0 AND  `moderateImprove` =0 AND  `noeffect` =0 AND  `worse` =0 AND `muchWorse` =0;

delete FROM `ct_condition_symptom` WHERE votes =0;


-- condition_cause
insert ignore into correlations(user, correlation, cause, effect, onsetDelay, durationOfAction)
select 2,(cc.votesPercent/100), (select c.varID from ct_causes c where c.causeID = cc.causeID),
(select con.varID from ct_conditions con where con.conID = cc.conID), 0, 86400 from intuitive_condition_cause_votes cc;


-- condition_symptom
insert ignore into correlations(user, correlation, cause, effect, onsetDelay, durationOfAction)
select 2,((cs.extreme + cs.severe + cs.moderate)/cs.votes), (select con.varID from ct_conditions con where con.conID = cs.conID),
 (select s.varID from ct_symptoms s where s.symID = cs.symID), 0, 86400 from ct_condition_symptom cs;


-- condition_treatment
insert ignore into correlations(user, correlation, cause, effect, onsetDelay, durationOfAction)
select 2,(-1 * (ct.majorImprove + ct.moderateImprove) / ( ct.majorImprove + ct.moderateImprove + ct.noeffect + ct.worse + ct.muchWorse )),
(select t.varID from ct_treatments t where t.treID = ct.treID),
(select con.varID from ct_conditions con where con.conID = ct.conID), 0, 86400 from ct_condition_treatment ct;


-- treatment_sideeffect
insert ignore into correlations(user, correlation, cause, effect, onsetDelay, durationOfAction)
select 2,(ts.votesPercent/100), (select s.varID from ct_sideeffects s where s.seID = ts.seID),
(select t.varID from ct_treatments t where t.treID = ts.treID), 0, 86400 from ct_treatment_sideeffect ts;



UPDATE variables v SET v.public = 1 where v.id in (select c.varID from ct_conditions c) or
v.id in (select s.varID from ct_sideeffects s) or
v.id in (select sym.varID from ct_symptoms sym) or
v.id in (select t.varID from ct_treatments t) or
v.id in (select ca.varID from ct_causes ca)
where c.cause in (select c.varID from ct_conditions c) or
v.id in (select s.varID from ct_sideeffects s) or
v.id in (select sym.varID from ct_symptoms sym) or
v.id in (select t.varID from ct_treatments t) or
v.id in (select ca.varID from ct_causes ca)

