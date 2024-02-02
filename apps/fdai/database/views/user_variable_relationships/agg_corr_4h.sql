CREATE VIEW `agg_corr_4h` AS
select ac.aggregate_qm_score,
       ac.data_source_name,
       cv.name as cName,
       ev.name as eName
from global_variable_relationships ac
join variables cv on ac.cause_variable_id = cv.id
join variables ev on ac.effect_variable_id = ev.id
order by ac.aggregate_qm_score desc
limit 10
