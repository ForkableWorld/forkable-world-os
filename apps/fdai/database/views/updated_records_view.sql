SELECT
  (SELECT COUNT(*) FROM correlations WHERE updated_at > NOW() - INTERVAL 1 DAY) as updatedCorrelations,
  (SELECT COUNT(*) FROM global_variable_relationships WHERE updated_at > NOW() - INTERVAL 1 DAY) as updatedGlobalVariableRelationships,
  (SELECT COUNT(*) FROM user_variables WHERE updated_at > NOW() - INTERVAL 1 DAY) as updatedUserVariables,
  (SELECT COUNT(*) FROM variables WHERE updated_at > NOW() - INTERVAL 1 DAY) as updatedVariables,
    (SELECT COUNT(*) FROM wp_users WHERE created_at > NOW() - INTERVAL 1 DAY) as newUsers,
        (SELECT COUNT(*) FROM measurements WHERE created_at > NOW() - INTERVAL 1 DAY) as newMeasurements,
  (SELECT COUNT(*) FROM connections WHERE updated_at > NOW() - INTERVAL 1 DAY) as updatedConnctions;
