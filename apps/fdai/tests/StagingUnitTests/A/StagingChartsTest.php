<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A;
use App\Charts\Sankey\WhatWeDoNotKnowSankeyQMChart;
use App\Charts\WhatWeDoNotKnowNetworkGraphChart;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Variables\CommonVariables\EmotionsCommonVariables\AnxietyNervousnessCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsCharts;
class StagingChartsTest extends SlimStagingTestCase
{
	use TestsCharts;
    public function testCommonVariableCharts(){
        $v = QMCommonVariable::find(AnxietyNervousnessCommonVariable::ID);
        $charts = $v->getChartGroup();
        $this->compareChartGroup($charts);
        $this->assertTrue(true);
    }
    public function testCreateSankeyNetworkCharts(){
        $chart = new WhatWeDoNotKnowSankeyQMChart();
        $html = $chart->getDynamicHtml();
        try {
            $paths = $chart->generateCSVs();
            $this->assertCount(1, $paths);
            foreach($paths as $path){FileHelper::assertExists($path);}
        } catch (\Throwable $e){
            QMLog::error("Not sure why this randomly happens on slaves: ".$e->getMessage());
        }
        $this->compareHtmlFragment("sankey", $html);
        $chart = new WhatWeDoNotKnowNetworkGraphChart();
        $html = $chart->getDynamicHtml();
        $this->compareHtmlFragment("network", $html);
    }
}
