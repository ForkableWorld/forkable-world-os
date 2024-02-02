create or replace view user_correlations_aggregated as
	select any_value(`uc`.`cause_variable_category_id`)                                                            AS `cause_variable_category_id`,
       any_value(`uc`.`effect_variable_category_id`)                                                           AS `effect_variable_category_id`,
       max(`uc`.`analysis_ended_at`)                                                                           AS `newest_data_at`,
       (sum((`uc`.`average_effect` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `average_effect`,
       (sum((`uc`.`average_daily_low_cause` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `average_daily_low_cause`,
       (sum((`uc`.`average_daily_high_cause` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `average_daily_high_cause`,
       (sum((`uc`.`forward_pearson_correlation_coefficient` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `forward_pearson_correlation_coefficient`,
       (sum((`uc`.`reverse_pearson_correlation_coefficient` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `reverse_pearson_correlation_coefficient`,
       (sum((`uc`.`predictive_pearson_correlation_coefficient` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `predictive_pearson_correlation_coefficient`,
       group_concat(distinct `uc`.`data_source_name` order by `uc`.`data_source_name` ASC separator
                    ', ')                                                                                      AS `data_source_name`,
       (sum((`uc`.`value_predicting_high_outcome` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `value_predicting_high_outcome`,
       (sum((`uc`.`value_predicting_low_outcome` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `value_predicting_low_outcome`,
       (sum((`uc`.`optimal_pearson_product` * `uc`.`statistical_significance`)) /
        sum(`uc`.`statistical_significance`))                                                                  AS `optimal_pearson_product`,
       avg(`uc`.`average_effect_following_high_cause`)                                                         AS `average_effect_following_high_cause`,
       avg(`uc`.`average_effect_following_low_cause`)                                                          AS `average_effect_following_low_cause`,
       avg(`uc`.`cause_baseline_average_per_day`)                                                              AS `cause_baseline_average_per_day`,
       avg(`uc`.`cause_baseline_average_per_duration_of_action`)                                               AS `cause_baseline_average_per_duration_of_action`,
       avg(`uc`.`cause_treatment_average_per_day`)                                                             AS `cause_treatment_average_per_day`,
       avg(`uc`.`cause_treatment_average_per_duration_of_action`)                                              AS `cause_treatment_average_per_duration_of_action`,
       avg(`uc`.`confidence_interval`)                                                                         AS `confidence_interval`,
       avg(`uc`.`critical_t_value`)                                                                            AS `critical_t_value`,
       avg(`uc`.`duration_of_action`)                                                                          AS `duration_of_action`,
       avg(`uc`.`effect_baseline_average`)                                                                     AS `effect_baseline_average`,
       avg(`uc`.`effect_baseline_relative_standard_deviation`)                                                 AS `effect_baseline_relative_standard_deviation`,
       avg(`uc`.`effect_baseline_standard_deviation`)                                                          AS `effect_baseline_standard_deviation`,
       avg(`uc`.`effect_follow_up_average`)                                                                    AS `effect_follow_up_average`,
       avg(`uc`.`effect_follow_up_percent_change_from_baseline`)                                               AS `effect_follow_up_percent_change_from_baseline`,
       avg(
               `uc`.`grouped_cause_value_closest_to_value_predicting_high_outcome`)                            AS `grouped_cause_value_closest_to_value_predicting_high_outcome`,
       avg(
               `uc`.`grouped_cause_value_closest_to_value_predicting_low_outcome`)                             AS `grouped_cause_value_closest_to_value_predicting_low_outcome`,
       avg(`uc`.`onset_delay`)                                                                                 AS `onset_delay`,
       avg(`uc`.`p_value`)                                                                                     AS `p_value`,
       avg(`uc`.`predicts_high_effect_change`)                                                                 AS `predicts_high_effect_change`,
       avg(`uc`.`predicts_low_effect_change`)                                                                  AS `predicts_low_effect_change`,
       avg(`uc`.`statistical_significance`)                                                                    AS `statistical_significance`,
       avg(`uc`.`t_value`)                                                                                     AS `t_value`,
       avg(`uc`.`z_score`)                                                                                     AS `z_score`,
       count(distinct `uc`.`id`)                                                                               AS `number_of_correlations`,
       count(distinct `uc`.`user_id`)                                                                          AS `number_of_users`,
       floor(avg(`uc`.`number_of_pairs`))                                                                      AS `number_of_pairs`,
       min(`uc`.`created_at`)                                                                                  AS `created_at`,
       max(`uc`.`updated_at`)                                                                                  AS `updated_at`,
       max(`uc`.`analysis_ended_at`)                                                                           AS `analysis_ended_at`,
       sum(`uc`.`cause_changes`)                                                                               AS `cause_changes`,
       sum(`uc`.`effect_changes`)                                                                              AS `effect_changes`,
       `uc`.`cause_variable_id`                                                                                AS `cause_id`,
       `uc`.`cause_variable_id`                                                                                AS `cause_variable_id`,
       `uc`.`effect_variable_id`                                                                               AS `effect_id`,
       `uc`.`effect_variable_id`                                                                               AS `effect_variable_id`
from `correlations` `uc`
where ((`uc`.`cause_variable_category_id` not in (20, 4)) and (`uc`.`effect_variable_category_id` not in (20, 4)))
group by (`uc`.`cause_variable_id` and `uc`.`effect_variable_id`)
having (`number_of_users` > 1);

