<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\TigerViewConnector;
use App\Variables\CommonVariables\GoalsCommonVariables\DailyAverageGradeCommonVariable;
use Tests\ConnectorTests\ConnectorTestCase;
class TigerViewTest extends ConnectorTestCase {
    protected const DISABLED_UNTIL = TigerViewConnector::TESTING_DISABLED_UNTIL;
    public function testTigerView() {
		$this->skipTest("TODO: Implement");
        if(!TigerViewConnector::ENABLED){
            $this->skipTest("Tigerview disabled for summer");
        }
        $this->connectorName = TigerViewConnector::NAME;
		$this->credentials = [
			'username'  => TigerViewConnector::TEST_USERNAME,
			'password'  => TigerViewConnector::TEST_PASSWORD,
		];
		$this->fromTime = time() - 14 * 86400;
        //$subjects = TigerViewConnector::SUBJECTS;
        $subjects = [
            //'Physical Ed',
            //'Health',
            //'Agriculture',
            //'Soc Studies',
            'Language Arts',
            //'Science',
            //'Math',
            'Algebra',
        ];
        foreach ($subjects as $subject) {
	        $this->variablesToCheck[] = $subject . TigerViewConnector::CLASS_DAILY_AVERAGE_GRADE_SUFFIX;
	        $this->variablesToCheck[] = TigerViewConnector::CURRENT_AVERAGE_GRADE_PREFIX . $subject;
        }
		$this->variablesToCheck = array (
			0 => 'Current Quarterly Average Grade for Early Bird PE Sem',
			1 => 'Current Quarterly Average Grade for Art & Design',
			2 => 'Art & Design Class Daily Average Grade',
			3 => 'Art & Design Class Assignment',
			4 => 'Current Quarterly Average Grade for Business & Economics',
			5 => 'Current Quarterly Average Grade for Earth Science',
			6 => 'Current Quarterly Average Grade for Lit & Comp',
			7 => 'Tardy for Class',
			8 => 'Lit & Comp Class Daily Average Grade',
			9 => 'Lit & Comp Class Assignment',
			10 => 'Current Quarterly Average Grade for Health Sem',
			11 => 'Health Sem Class Daily Average Grade',
			12 => 'Health Sem Class Assignment',
			13 => 'Current Quarterly Average Grade for High School Algebra',
			14 => 'Daily Screen Time Allowance',
			15 => 'Daily Sugar Allowance',
		);
        $this->connectImportDisconnect();
        $v = DailyAverageGradeCommonVariable::getUserVariableByUserId(1);
        $m = $v->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertGreaterThan(0, count($m));
    }
}
