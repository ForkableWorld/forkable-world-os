<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\WithingsConnector;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatFreeMassFfmOrLeanBodyMassLbmCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatMassWeightCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatRatioCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureDiastolicBottomNumberCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureSystolicTopNumberCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\HeartRatePulseCommonVariable;
use Tests\ConnectorTests\ConnectorTestCase;
class WithingsTest extends ConnectorTestCase{
    public $connectorName = WithingsConnector::NAME;
    public const DISABLED_UNTIL = "2021-09-01";
    protected $variablesToCheck = [
        BodyWeightCommonVariable::NAME,
        //'Height',
        FatFreeMassFfmOrLeanBodyMassLbmCommonVariable::NAME,
        FatRatioCommonVariable::NAME,
        FatMassWeightCommonVariable::NAME,
        BloodPressureDiastolicBottomNumberCommonVariable::NAME,
        BloodPressureSystolicTopNumberCommonVariable::NAME,
        HeartRatePulseCommonVariable::NAME,
        //DailyStepCountCommonVariable::NAME,
        //WalkOrRunDistanceCommonVariable::NAME,
        //CaloriesBurnedCommonVariable::NAME,
        //'Elevation',
    ];
    public function testWithings(){
        if($this->weShouldSkip()){return;}
        $this->fromTime = time() - 60 * 86400;
        //$this->fromTime = time() - 63113852; // TODO: Delete this line
        $this->connectImportCheckDisconnect();
    }
}
