<?php
namespace Storage;
use App\Storage\DB\Migrations;
use Tests\UnitTestCase;

class MigrationGeneratorTest extends UnitTestCase
{
    public function testMigrationGenerator(){
        $this->skipTest("TODO");
        $url = Migrations::getUrlToCreateIndexMigration("select * from measurements
            where deleted_at is null
            and user_id=1");
        $this->assertContains("name=measurements_deleted_at_user_id_index", $url);
    }
}
