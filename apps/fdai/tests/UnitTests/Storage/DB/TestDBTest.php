<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Tests\UnitTests\Storage\DB;

use App\Storage\DB\TestDB;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Storage\DB\TestDB
 */
class TestDBTest extends UnitTestCase
{

    public function testGetLastImported()
    {
        $this->skipTest("We don't need this anymore");
        TestDB::importAndMigrateTestDB();
        $since = TestDB::getSecondsSinceLastImported();
        $this->assertLessThan(2, $since);

    }
}
