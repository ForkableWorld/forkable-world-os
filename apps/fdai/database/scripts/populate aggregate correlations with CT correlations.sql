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
  'CT'
FROM
  correlations
WHERE correlations.user_id = 2
