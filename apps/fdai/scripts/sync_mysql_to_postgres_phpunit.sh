#!/usr/bin/env bash

php vendor/phpunit/phpunit/phpunit --configuration phpunit.xml --filter "/(Tests\\Unit\\MySQLPostgresSyncTest::test_sync_mysql_to_postgres)( .*)?$/" --test-suffix MySQLPostgresSyncTest.php tests/Unit --teamcity
