<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\QMUnits;

class UnitCategoriesTest extends \Tests\SlimTests\SlimTestCase {

    public function testGetUnitCategories(){
        $this->setAuthenticatedUser(1);
        $arr = $this->getAndDecodeBody('/api/unitCategories');
        $this->assertGreaterThan(12, count($arr));
    }
}
