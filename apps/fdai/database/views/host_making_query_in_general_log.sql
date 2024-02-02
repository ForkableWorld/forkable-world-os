SELECT count(*) as numberOfQueries, user_host from general_log
where event_time > NOW() - INTERVAL 1 HOUR
AND argument = "select * from `tracker_sessions` where `uuid` = ? limit 1"
group by user_host ORDER BY numberOfQueries DESC LIMIT 100