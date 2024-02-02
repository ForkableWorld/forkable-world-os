SELECT
  `reverse correlations`.`cause_var` AS `cause_var`,
  `reverse correlations`.`effect_var` AS `effect_var`,
  avg(
      `reverse correlations`.`correlation`
  ) AS `avgCorrelation`,
  `reverse correlations`.`value_predicting_high_outcome` AS `value_predicting_high_outcome`,
  `reverse correlations`.`value_predicting_low_outcome` AS `value_predicting_low_outcome`,
  `reverse correlations`.`causeUnit` AS `causeUnit`,
  `reverse correlations`.`user` AS `user`,
  `reverse correlations`.`statisticalSignificance` AS `statisticalSignificance`,
  `reverse correlations`.`numberOfPairs` AS `numberOfPairs`,
  `reverse correlations`.`calculatedTimestamp` AS `calculatedTimestamp`,
  `reverse correlations`.`durationOfAction` AS `durationOfAction`,
  `reverse correlations`.`absoluteCorrelation` AS `absoluteCorrelation`,
  `reverse correlations`.`onsetDelay` AS `onsetDelay`,
  `reverse correlations`.`effectid` AS `effectid`,
  `reverse correlations`.`causeid` AS `causeid`
FROM
  `reverse correlations`
GROUP BY
  `reverse correlations`.`cause_var`,
  `reverse correlations`.`effect_var`,
  `reverse correlations`.`user`