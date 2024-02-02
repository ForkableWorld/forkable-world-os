<?php /** @noinspection RedundantSuppression */
/** @noinspection DuplicatedCode */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests;
use App\Buttons\Admin\PHPStormButton;
use App\DevOps\XDebug;
use App\Files\FileHelper;
use App\Files\PHP\BaseModelFile;
use App\Models\GlobalVariableRelationship;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\IpDatum;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Storage\DB\QMDB;
use App\Storage\DB\TestDB;
use App\Utils\EnvOverride;
use Tests\UnitTestCase;
class DBTest extends UnitTestCase {
	public function testMigrateDB(): void{
		if(!XDebug::active() || !EnvOverride::isLocal()){
			$this->skipTest("This test is not needed");
		}
		TestDB::importAndMigrateTestDB();
	}
    public function testJsonExport(){
	    $this->skipTest("This test is not needed");
        $content = Connector::query()->first()->exportToJson(JSON_PRETTY_PRINT);
        FileHelper::writeByFilePath('data/connectors.json', $content);
    }
    public function testUpdateTestDB(): void{
		$this->skipTest("This test is not needed");
        TestDB::importAndMigrateTestDB(); // Always import and migrate here in case import is disabled in .env
	    UserVariableRelationship::newModelInstance()->update([UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT => null]);
	    UserVariable::newModelInstance()->update([UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT => null]);
	    Variable::newModelInstance()->update([UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT => null]);
	    GlobalVariableRelationship::newModelInstance()->update([UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT => null]);
	    $aggregateCorrelations = GlobalVariableRelationship::all();
	    foreach($aggregateCorrelations as $aggregateCorrelation){
		    $aggregateCorrelation->analyzeFully(__FUNCTION__);
	    }
	    TestDB::copyStorageToFixtures();
        //QMDB::updateDBConstants();
        //TestDB::dumpTestDB();
    }

    public function testDumpTestDb(): void{
	    $this->skipTest("This test is not needed");
        TestDB::dumpTestDB();
    }
    public function testDumpTestDbTable(): void{
	    $this->skipTest("This test is not needed");
        TestDB::dumpTestTable(IpDatum::TABLE);
    }
    public function testUpdateModels(){
	    $this->skipTest("This test is not needed");
        BaseModelFile::updateModelsWithColumn('is_public');
    }
    public function testUpdateDBConstants(): void{
	    $this->skipTest("This test is not needed");
        QMDB::updateDBConstants();
    }
    public function testDumpStructure(): void{
	    $this->skipTest("This test is not needed");
        TestDB::dumpDBStructure();
    }
    public function testGenerateLaravelModels(): void{
	    $this->skipTest("This test is not needed");
        if(!XDebug::active()){
            le("Please run with xDebug so ide helper service provider is loaded!");
        }
        //BaseModelFile::updatePHPDocs();
	    BaseModelFile::generateByTable('shell_commands');
	    BaseModelFile::generateByTable('shell_executions');
	    //BaseModelFile::generateModels();
    }
    public static function getLinkToUpdateTestDB(): string {
        return PHPStormButton::redirectUrl();
    }
}
