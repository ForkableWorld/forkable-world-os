<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Correlations\UserVariableRelationships;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Properties\Measurement\MeasurementValueProperty;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class ChartHtmlForCorrelationWithoutEnoughDataTest extends SlimStagingTestCase
{
    public function testChartHtmlForCorrelationWithoutEnoughData(){
        /** @var QMUserVariable $cause */
        $cause = QMUserVariable::findInDatabaseByNameOrVariableId(230, 5999170);
        $source = $cause->getBestDataSource();
        $this->assertEquals(QuantiModoConnector::DISPLAY_NAME, $source->displayName);
        $c = QMUserVariableRelationship::getOrCreateUserVariableRelationship(65181, 'Fat Intake',
            'Alertness');
        $fat = $c->getOrSetCauseQMVariable();
        $invalid = $fat->getInvalidMeasurements();
        $this->assertArrayEquals(array (
	        0 => 18766,
	        1 => 6274,
        ), MeasurementValueProperty::pluckArray($invalid));
        $alertness = $c->getEffectQMVariable();
        $invalid = $alertness->getInvalidMeasurements();
        $this->assertCount(0, $invalid);
        $this->assertEquals(1, $alertness->getMinimumAllowedValueAttribute());
        $charts = $c->getChartGroup();
        $this->compareHtmlFragment("DynamicCharts", $charts->getHtmlWithDynamicCharts(false));
        $this->compareHtmlFragment("ChartHtmlWithEmbeddedImageOrReasonForFailure",
            $charts->getChartHtmlWithEmbeddedImageOrReasonForFailure());
    }
}
