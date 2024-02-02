<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Variables;
use App\Models\Variable;

class VariableCategoriesTest extends \Tests\SlimTests\SlimTestCase {
    public function testGetVariableCategories(){
        $this->setAuthenticatedUser(1);
        $apiUrl = '/api/variableCategories';
        $parameters = [];
        $response = $this->slimGet($apiUrl, $parameters);
        $variableCategories = json_decode($response->getBody(), true);
        foreach($variableCategories as $variableCategory){
            $this->assertArrayHasKey('id', $variableCategory);
            $this->assertArrayHasKey('name', $variableCategory);
            $this->assertArrayHasKey('fillingValue', $variableCategory);
            $this->assertArrayHasKey('maximumAllowedValue', $variableCategory);
            $this->assertArrayHasKey('minimumAllowedValue', $variableCategory);
            $this->assertArrayHasKey('durationOfAction', $variableCategory);
            $this->assertArrayHasKey('onsetDelay', $variableCategory);
            $this->assertArrayHasKey('combinationOperation', $variableCategory);
            $this->assertArrayHasKey('causeOnly', $variableCategory);
            $this->assertArrayHasKey('isPublic', $variableCategory);
            $this->assertArrayHasKey(Variable::FIELD_OUTCOME, $variableCategory);
            $this->assertArrayHasKey('defaultUnitId', $variableCategory);
            $this->assertArrayHasKey('imageUrl', $variableCategory);
        }
    }
}
