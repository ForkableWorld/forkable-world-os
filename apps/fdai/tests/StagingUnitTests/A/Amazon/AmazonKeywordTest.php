<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Amazon;
use App\Products\Product;
use App\Products\ProductHelper;
use App\VariableCategories\FoodsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
use Tests\SlimStagingTestCase;
use Tests\UnitTests\Products\AmazonTest;
class AmazonKeywordTest extends SlimStagingTestCase {
    public function testAmazonKeyword(){
        if(time() < strtotime(AmazonTest::DISABLED_UNTIL)){
            $this->skipTest('Waiting for Amazon to approve use of US product API at https://affiliate-program.amazon.com/assoc_credentials/home');
            return;
        }
        $term = "Banana";
        $product = ProductHelper::getByKeyword($term, FoodsVariableCategory::NAME);
        $this->assertContains($term, $product->getVariableName());
        $upc = "037000947714";
        $product = ProductHelper::getByUpc($upc);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertContains("Crest 3D White Fluoride Anticavity Toothpaste Radiant Mint", $product->getVariableName());
        $variablesFromDb = QMCommonVariable::getVariablesFromDbByUpc($upc);
        $this->assertCount(2, $variablesFromDb);
        $variables = QMVariable::getCommonOrUserVariablesFromUpc($upc);
        $this->assertCount(2, $variables);
    }
}
