Fixtures
=====================

We have xml fixtures for specific test cases in this folder which supplies data for them. 
To create a new fixture, run:
`bash scripts/export_mysql_table_as_phpunit_xml.sh`
from the root of the repository.  Change the table name in the script first. 

```mysqldump --max-allowed-packet=16M --no-create-info --xml -u root -p database_name table_name > output_file.xml```

real example
`mysqldump --max-allowed-packet=16M --no-create-info --xml -uhomestead -psecret quantimodo_test user_variables > /home/vagrant/qm-api/tests/fixtures/common/user_variables.xml`

`--no-create-info` is required because we don't need the create table and fields in the xml file.

After creating it, don't forget to include it in your test class

```
    protected $fixtureFiles = [
        'meaningful_name'            => 'path/to/fixture.xml',
    ];
```
