<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Storage\DB\TestDB;
use App\VariableCategories\SymptomsVariableCategory;
class FilterOperatorTest extends \Tests\SlimTests\SlimTestCase {

    public function testValueFilters() {
        $earliest = 1407019860;
        TestDB::deleteUserData();
        TestQueryLogFile::flushTestQueryLog();
        $this->post4SymptomMeasurements($earliest);
        $value = 2;
        $params = [
            'user'         => 1,
            'variableName' => $this->getName(),
            'startTime'    => "(gte)".($earliest - SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS),
        ];
        //equal filter test
        $params['value'] = $value;
        $responseObject = $this->getMeasurements($params, 1);
        $this->assertEquals($value, $responseObject[0]->value);
        //less than filter test
        $params['value'] = '(lt)'.$value;
        $responseObject = $this->getMeasurements($params, 1);
        foreach ($responseObject as $measurement) {
            $this->assertLessThan($value, $measurement->value);
        }
        $this->assertQueryCountLessThan(31);
    }

}
