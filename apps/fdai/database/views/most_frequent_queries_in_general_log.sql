SELECT count(*) as numberOfQueries, argument from general_log
where event_time > NOW() - INTERVAL 1 HOUR
group by argument ORDER BY numberOfQueries DESC LIMIT 100