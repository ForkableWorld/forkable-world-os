SELECT
  FROM_UNIXTIME(m.timestamp) as "Date",
  AVG(CASE WHEN v.name="Overall Mood" THEN m.value ELSE NULL END) AS "Average Overall Mood",
  AVG(CASE WHEN v.name="Wellbutrin" THEN m.value ELSE NULL END) AS "Average Wellbutrin"

FROM measurements m
JOIN variables v
ON m.variable = v.id

WHERE
  v.name IN ("Overall Mood", "Wellbutrin")
GROUP BY DATE(FROM_UNIXTIME(m.timestamp));