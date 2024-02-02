<?php
namespace Tests\UnitTests;
use App\Charts\CorrelationCharts\PairsOverTimeLineChart;
use App\UnitCategories\RatingUnitCategory;
use Tests\UnitTestCase;
class SwaggerDefinitionTest extends UnitTestCase
{
    public function testSwaggerDefinition(){
		$cat = new RatingUnitCategory();
		$def = $cat->getSwaggerDefinition();
		$this->assertNotNull($def->description);
        $def = (new PairsOverTimeLineChart())->getSwaggerDefinition();
        $this->assertNotNull($def);
    }
}
