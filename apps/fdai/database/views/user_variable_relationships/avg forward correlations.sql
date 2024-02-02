SELECT
  `forward correlations`.`cause_var` AS `cause_var`,
  `forward correlations`.`effect_var` AS `effect_var`,
  avg(
      `forward correlations`.`correlation`
  ) AS `avgCorrelation`,
  `forward correlations`.`value_predicting_high_outcome` AS `value_predicting_high_outcome`,
  `forward correlations`.`value_predicting_low_outcome` AS `value_predicting_low_outcome`,
  `forward correlations`.`causeUnit` AS `causeUnit`,
  `forward correlations`.`user` AS `user`,
  `forward correlations`.`statisticalSignificance` AS `statisticalSignificance`,
  `forward correlations`.`numberOfPairs` AS `numberOfPairs`,
  `forward correlations`.`calculatedTimestamp` AS `calculatedTimestamp`,
  `forward correlations`.`durationOfAction` AS `durationOfAction`,
  `forward correlations`.`absoluteCorrelation` AS `absoluteCorrelation`,
  `forward correlations`.`onsetDelay` AS `onsetDelay`,
  `forward correlations`.`effectid` AS `effectid`,
  `forward correlations`.`causeid` AS `causeid`
FROM
  `forward correlations`
GROUP BY
  `forward correlations`.`cause_var`,
  `forward correlations`.`effect_var`,
  `forward correlations`.`user`