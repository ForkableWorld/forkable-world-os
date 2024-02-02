## Recovery Protocol

To restore DB to an earlier point in time:

### Create restored instance
- Write down the latest time before the database was corrupted  
- Create restored instance to that time at  https://console.aws.amazon.com/rds/home -> Instance actions -> Restore to a point in time
- Replace url of corrupted instance throughout project with host url of new DB
- Run migrations on new instance by setting new DB_HOST .env
- Update SOURCE and DESTINATION host variables in database/delete_test_data_and_transfer_new_records_to_new_database.sh
- Run `bash database/delete_test_data_and_transfer_new_records_to_new_database.sh`
- Commit, push, and release to master
- Watch for release to complete
- Make sure new .env file is being used on production
- Uncomment and set LATEST_CREATED_AT_ON_DESTINATION manually to the time you did the last transfer
- Run `bash database/delete_test_data_and_transfer_new_records_to_new_database.sh` to transfer new records created during switchover
