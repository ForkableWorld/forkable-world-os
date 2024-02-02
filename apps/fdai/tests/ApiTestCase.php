<?php
namespace Tests;
use App\Storage\DB\TestDB;
class ApiTestCase extends QMBaseTestCase
{
    protected function setUp(): void{
        parent::setUp();
        $class = $this->getClassBeingTested();
        $class::query()->forceDelete();
    }
	protected function getAllowedDBNames(): array{return [TestDB::DB_NAME];}
}
