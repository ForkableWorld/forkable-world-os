<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Measurements;
use App\Models\Measurement;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Storage\DB\TestDB;
use App\Types\QMArr;
use App\Utils\Stats;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use Illuminate\Support\Arr;
use Tests\UnitTestCase;
class MeasurementsUnitTest extends UnitTestCase {
    protected function setUp(): void{
        parent::setUp();
        TestDB::deleteUserData();
    }
    public function testMergingMeasurements(){
        $v = OverallMoodCommonVariable::getUserVariableByUserId(1);
        $startTime = time();
        $this->createMeasurements($v, $startTime);
        $saved = $v->getQMMeasurements();
        $this->checkNewMeasurements($saved);
        $this->assertCount(1, $saved);
        $m = QMArr::first($saved);
        $this->assertEquals(3, $m->getValue());
        $this->assertEquals(Stats::roundToNearestMultipleOf($startTime,
            $v->getMinimumAllowedSecondsBetweenMeasurements()),
            $m->startTime);
        $meta = $m->getAdditionalMetaData();
        $this->assertCount(0, $meta->getMergedMeasurements());
        $this->assertCount(1, $v->getNewMeasurements());
        $byVariableName = $this->checkIndexingByVariableName($m);
        $this->checkFlattening($byVariableName);
    }
    /**
     * @param array $byVariableName
     */
    private function checkFlattening(array $byVariableName): void{
        /** @var QMMeasurement[] $flat */
        $flat = Arr::flatten($byVariableName, 1);
        $this->assertTrue(isset($flat[0]));
        $this->assertEquals(OverallMoodCommonVariable::NAME, $flat[0]->getVariableName());
    }
    /**
     * @param QMMeasurement $m
     * @return array
     */
    private function checkIndexingByVariableName(QMMeasurement $m): array{
        $byVariableName =
            QMMeasurement::getIndexedByVariableName([Measurement::FIELD_START_TIME => "(gte)".$m->startTime]);
        $this->assertCount(1, $byVariableName);
        $this->assertTrue(isset($byVariableName[OverallMoodCommonVariable::NAME]));
        return $byVariableName;
    }
    /**
     * @param QMUserVariable $v
     * @param int $startTime
     */
    private function createMeasurements(QMUserVariable $v, int $startTime): void{
        BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $v->addToMeasurementQueueIfNoneExist(new QMMeasurement($startTime, 3));
        $v->addToMeasurementQueueIfNoneExist(new QMMeasurement($startTime, 3));
        $v->saveMeasurements();
        $this->assertCount(1, $v->getQMMeasurements());
    }
	/**
	 * @param QMMeasurement[] $measurements
	 */
	protected function checkNewMeasurements(array $measurements){
		foreach($measurements as $m){
			$this->checkMeasurement($m);
		}
	}
	/**
	 * @param QMMeasurement $measurement
	 */
	protected function checkMeasurement(QMMeasurement $measurement): void{
		$row = $measurement->getDbRow();
		$this->assertPropertyGreaterThanAnHourAgo($row, "created_at");
	}
}
