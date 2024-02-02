update global_variable_relationships 
set aggregate_qm_score =            
                abs(correlation) * IFNULL(average_vote, 1) *
                (1 - exp(-number_of_pairs / 100)) * (1 - exp(-number_of_users / 5))
