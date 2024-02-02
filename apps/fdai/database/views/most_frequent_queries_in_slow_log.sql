SELECT count(*) as numberOfQueries, sql_text from slow_log
where start_time > NOW() - INTERVAL 1 HOUR
group by sql_text ORDER BY numberOfQueries DESC LIMIT 100