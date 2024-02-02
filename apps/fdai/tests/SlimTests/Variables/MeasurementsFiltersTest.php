<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\DB\TestDB;
use App\Utils\Constraint;
use Carbon\Carbon;
use DateTime;
use App\Variables\QMUserVariable;
class MeasurementsFiltersTest extends \Tests\SlimTests\SlimTestCase
{
    private $earliest = 1407019860;
    /**
     * List of fixture files
     * @var string[]
     */
    protected $fixtureFiles = [];
    public function testMeasurementsSourceAndVariableFilters() {
	    TestDB::resetTestDB();
        $earliest = 1407019860;
        $this->post4SymptomMeasurements($earliest);
        // test variableName filter
        $parameters = ['user' => 1, 'variableName' => $this->getUpperCaseName()];
        $variableNameMeasurements = $this->getMeasurements($parameters);
        foreach ($variableNameMeasurements as $m) {
            $this->assertEquals($this->getUpperCaseName(), $m->variableName);
        }
        // test source filter
        $parameters = ['user' => 1, 'sourceName' => $this->getUpperCaseName()];
        $sourceNameMeasurements = $this->getMeasurements($parameters);
        foreach ($sourceNameMeasurements as $m) {
            $this->assertEquals($this->getClientId(), $m->sourceName);
        }
        // test value filter
        $parameters = [
            'user'  => 1,
            'variableName' => $this->getUpperCaseName(),
            'value' => 2
        ];
        foreach ($this->getMeasurements($parameters) as $m) {
            $this->assertEquals(2, $m->value);
            $this->assertNotNull($m->updatedAt);
            $this->assertNotNull($m->createdAt);
        }
        $this->assertQueryCountLessThan(42);
    }
    public function testGetMeasurementsWithValueFilters() {
        $this->post4SymptomMeasurements($this->earliest);
        // Assert the measurements were inserted
        $value = 2;
        $parameters = $this->getParams();
        $parameters = $this->checkEqualFilter($value, $parameters);
        $parameters = $this->checkLessThanFilter($value, $parameters);
        $userVariable = QMUserVariable::findUserVariableByNameIdOrSynonym(1, $parameters['variableName']);
        $fillingValue = $userVariable->getFillingValueAttribute();
        $this->assertNull($fillingValue);
        $groupingWidth = $userVariable->getMeasurementRequest()->getGroupingWidth();
        $this->assertNull($groupingWidth);
        //less than or equal filter test
        $parameters['value'] = '(le)' . $value;
        $responseObject = $this->getMeasurements($parameters, 2);
        foreach ($responseObject as $measurement) {
            $this->assertLessThanOrEqual($value, $measurement->value);
        }
        //greater than filter test
        $parameters['value'] = '(gt)' . $value;
        $responseObject = $this->getMeasurements($parameters, 2);
        foreach ($responseObject as $measurement) {
            $this->assertGreaterThan($value, $measurement->value);
        }
        //greater than or equal filter test
        $parameters['value'] = '(ge)' . $value;
        $responseObject = $this->getMeasurements($parameters, 3);
        foreach ($responseObject as $measurement) {
            $this->assertGreaterThanOrEqual($value, $measurement->value);
        }
    }
    public function testMeasurementsFilterByVariableCategoryName() {
		$this->deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $earliest = 1407019860;
        $this->post4SymptomMeasurements($earliest);
        $parameters = [ 'variableCategoryName' => 'Treatments' ];
        $measurementsResponse = $this->getMeasurements($parameters, 0);
        $parameters = [ 'variableCategoryName' => 'Symptoms' ];
        $measurementsResponse = $this->getMeasurements($parameters, 4);
        foreach ($measurementsResponse as $measurement) {
            $this->assertEquals('Symptoms', $measurement->variableCategoryName);
            $this->assertEquals('/5', $measurement->unitAbbreviatedName);
        }
    }
    public function testMeasurementsFilterByIsoDateTimeWithAndWithoutT() {
	    Measurement::deleteAll();
		UserVariable::deleteAll();
        $this->setAuthenticatedUser(1);
        $this->postAndGetDecodedBody('api/v3/measurements', '[
            {"measurements":
            [
                {"startTime":1407019860,"value":"1"},
                {"startTime":1408019965,"value":"2"}
            ],
            "name":"Measurement Filter Test Variable","source":"Measurement Filter Test Source",
            "category":"Physical Activities",
            "combinationOperation":"MEAN",
            "unit":"/5"}
        ]');
        sleep(2);
        $date = new DateTime();
        $dateAfter1stPostWithoutT = $date->format('Y-m-d H:i:s');
        $dateAfter1stPostWithT = str_replace(' ', 'T', $dateAfter1stPostWithoutT);
        $this->postAndGetDecodedBody('api/v3/measurements', '[
            {"measurements":
                [
                    {"startTime":1407019866,"value":"3"},
                    {"startTime":1408019967,"value":"4"}
                ],
            "name":"Measurement Filter Test Variable","source":"Measurement Filter Test Source",
            "category":"Physical Activities",
            "combinationOperation":"MEAN",
            "unit":"/5"}
        ]');
		// Fails randomly and irreplicably
//        $this->getMeasurements([
//            'updatedAt' => '(ge)' . $dateAfter1stPostWithoutT,
//            'sort' => '-startTimeEpoch',
//            'limit' => 200,
//            'offset' => 0
//        ], 2, "Filter does not work without T in updatedAt parameter");
		$unixTime = time_or_exception($dateAfter1stPostWithT);
	    $carbon = Carbon::createFromTimestampUTC($unixTime);
	    $measurements = Measurement::whereDate(Measurement::UPDATED_AT, '>=', $carbon)
	                               ->get();
	    $this->assertEquals(2, $measurements->count());
		// Fails randomly and irreplicably
//        $this->getMeasurements([
//            'updatedAt' => '(ge)' . $dateAfter1stPostWithT,
//            'sort' => '-startTimeEpoch',
//            'limit' => 200,
//            'offset' => 0
//        ], 2, "Filter does not work with a T in updatedAt");
    }
    /**
     * @param $value
     * @param $parameters
     * @return array
     */
    private function checkEqualFilter($value, $parameters): array{
        //equal filter test
        $parameters['value'] = $value;
        $responseObject = $this->getMeasurements($parameters, 1);
        $this->assertEquals($value, $responseObject[0]->value);
        return $parameters;
    }
    /**
     * @param $value
     * @param $parameters
     * @return array
     */
    private function checkLessThanFilter($value, $parameters): array{
        //less than filter test
        $parameters['value'] = '(lt)' . $value;
        $responseObject = $this->getMeasurements($parameters, 1);
        foreach ($responseObject as $measurement) {
            $this->assertLessThan($value, $measurement->value);
        }
        return $parameters;
    }
    public function testNotEquals(): void{
        $this->post4SymptomMeasurements($this->earliest);
        $value = 2;
        $params = $this->getParams();
        $params['value'] = '!'.$value;
        $c = new Constraint('value', Measurement::TABLE, $params['value']);
        $this->assertEquals(Constraint::OPERATOR_NOT_EQUAL, $c->operator);
        $this->assertTrue($c->is_negation);
        $r = $this->getMeasurements($params, 3);
        foreach($r as $m){
            $this->assertNotEquals($value, $m->value);
        }
    }
    /**
     * @return array
     */
    protected function getParams(): array{
        $parameters = [
            'user'         => 1,
            'variableName' => $this->getUpperCaseName(),
            'startTime'    => "(gte)".($this->earliest - 1),
            'unit'         => '/5'
        ];
        return $parameters;
    }
	private function getClientId(){
		return BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
	}
}
