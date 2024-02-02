<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Amazon;
use App\Products\ProductHelper;
use App\Properties\Variable\VariableNameProperty;
use Tests\SlimStagingTestCase;
use Tests\UnitTests\Products\AmazonTest;
class AmazonUPCTest extends SlimStagingTestCase {
    public function testAmazonUPC(){
        if(time() < strtotime(AmazonTest::DISABLED_UNTIL)){
            $this->skipTest('Waiting for Amazon to approve use of US product API at https://affiliate-program.amazon.com/assoc_credentials/home');
            return;
        }
        $upc = '029537049023';
        $name = "Nature's Bounty Lutein";
        $product = ProductHelper::getByUpc($upc);
        $this->assertNotNull($product);
        $paymentVariable = $product->getCommonPaymentVariable();
        $this->assertStringStartsWith(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX, $paymentVariable->displayName);
        $this->assertFalse(stripos($paymentVariable->displayName, VariableNameProperty::PAYMENT_VARIABLE_NAME_SUFFIX));
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals($name, $variable->name);
    }
}
