<?php
namespace Tests\UnitTests\Properties;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Properties\Measurement\MeasurementUserIdProperty;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Types\MySQLTypes;
use DB;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Properties\BaseProperty;
 */
class BasePropertyTest extends UnitTestCase {
	/**
	 * @covers BaseProperty::getSqlAddStatement
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetSqlAddStatementBaseProperty(){
		$obj = new MeasurementUserIdProperty();
		$this->assertEquals("user_id INT NOT NULL COMMENT 'Unique ID indicating the owner of the record'", $obj->getSqlDeclaration());
	}
    public function testCorrectTypeTraits(){
        $tables = Writable::getDBTables();
        foreach ($tables as $table){
            $class = BaseModel::getClassByTable($table);
            if(!class_exists($class)){continue;}
            $model = new $class;
            $properties = $model->getPropertyModels();
            foreach ($properties as $property){
                $this->assertTrue($property instanceof BaseProperty);
                try {
                    $this->fixDBTypeIfNecessary($property);
                } catch (\Throwable $e) {
                    le($e);
                }

                if($property->phpType === 'array'){
                    //$this->assertIsArrayProperty($property);
                }
            }
        }
    }

    private function assertHasTrait(BaseProperty $property, string $trait){
        $traits = class_uses($property);
        $parent = get_parent_class($property);
        $traits = array_merge($traits, class_uses($parent));
        $this->assertContains($trait, $traits, get_class($property) . ' does not have trait ' . $trait);
    }

    /**
     * @param $property
     * @return void
     */
    private function assertIsArrayProperty(BaseProperty $property): void
    {
        $this->assertTrue($property->isArray());
        $this->assertIsArray($property->getExample(), "Example for " . get_class($property) . " is not an array");
        $this->assertHasTrait($property, \App\Traits\PropertyTraits\IsArray::class);
        $this->assertEquals('array', $property->phpType);
        $DBColumn = $property->getDBColumn();
        $actualDBType = $DBColumn->getType();
        if($actualDBType->getName() !== MySQLTypes::TEXT){
            $this->fail("DBType for " . get_class($property) . " is not " . MySQLTypes::TEXT . " but " .
                $actualDBType->getName()."\n Please run this migration ". $DBColumn->createMigration());
        }
        if($actualDBType->getName() !== $property->dbType){
            $this->fixDBTypeIfNecessary($property, $actualDBType);

            $this->fail("Pleases set the dbType on " . get_class($property) . " to " . $actualDBType->getName());
        }
        $this->assertEquals($actualDBType->getName(), $property->dbType);
        $this->assertEquals(MySQLTypes::TEXT, $property->dbType);
    }

    /**
     * @param BaseProperty $property
     * @return void
     */
    private function fixDBTypeIfNecessary(BaseProperty $property): void
    {
        $file = $property->getPhpClassFile();
        try {
            $DBColumn = $property->getDBColumn();
        } catch (\Throwable $e) {
            $DBColumn = $property->getDBColumn();
            le($e);
        }
        $actualDBType = $DBColumn->getType();
        $actualDbType = $actualDBType->getName();
        $assignedDBType = $property->dbType;
        if($assignedDBType !== $actualDbType){
            if($actualDbType === MySQLTypes::DATETIME){
                $actualDBType = $DBColumn->getType();
                QMLog::debug("The assignedDBType '$assignedDBType' does not match the actual type '$actualDbType' on "
                    . get_class($property));
            } else {
                $file->replace('dbType = ' . $assignedDBType, 'dbType = ' . $actualDbType);
            }
        }
    }
}
