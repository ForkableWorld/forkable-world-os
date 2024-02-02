<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\Connectors\GoogleFitConnector;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Units\PoundsUnit;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use App\Variables\QMUserVariable;
use Tests\ConnectorTests\ConnectorTestCase;
class GoogleFitTest extends ConnectorTestCase {
    public $connectorName = GoogleFitConnector::NAME;
    public const DISABLED_UNTIL = "2023-04-01";
    public const REASON_FOR_SKIPPING = "Debugging this is a nightmare";
    public function testGoogleFit(){
        if($this->weShouldSkip()){return;}
        $this->fromTime = time() - 60 * 86400;
        $this->connectImportCheckDisconnect();
        if(time() > strtotime("2020-01-01")){
            $this->checkBodyWeightVariable(); // Stopped returning weight for some reason
        }
    }
    protected function checkBodyWeightVariable(): void{
        $v = QMUserVariable::getByNameOrId($this->getUserId(), BodyWeightCommonVariable::NAME);
        $measurements = $v->getProcessedMeasurementsInUserUnit();
        $this->assertGreaterThan(1, count($measurements), "Weigh yourself on the Withings scale");
        foreach($measurements as $m){
            $v->logInfo("$m->value $m->unitAbbreviatedName");
            $this->assertEquals(BaseCombinationOperationProperty::COMBINATION_SUM, $v->getOrSetCombinationOperation());
            $this->assertEquals(PoundsUnit::NAME, $v->getUserUnit()->getNameAttribute());
            $this->assertGreaterThan(100, $m->value);
            //$this->assertLessThan(140, $m->value);
        }
    }
}
