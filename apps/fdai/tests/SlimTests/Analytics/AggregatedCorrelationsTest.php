<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection ArgumentEqualsDefaultValueInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Analytics;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Logging\ConsoleLog;
use App\Logging\QMLogLevel;
use App\Utils\Env;
class AggregatedCorrelationsTest extends \Tests\SlimTests\SlimTestCase {
    public function testDurationOfActionCorrelationsFilterParams(){
        $this->setAuthenticatedUser(1);
        $apiUrl = '/api/user_variable_relationships';
        // durationOfAction parameter test
        $parameters = ['effectVariableName' => 'Body Mass Index Or BMI', 'durationOfAction' => 86400];
        $aggregateCorrelations = $this->getAndDecodeBody($apiUrl, $parameters);
        foreach ($aggregateCorrelations as $aggregateCorrelation) {
            $this->assertEquals(86400, $aggregateCorrelation->durationOfAction);
            $this->checkUserVariableRelationshipObject($aggregateCorrelation);
        }
    }
    /**
     * @param QMGlobalVariableRelationship $c
     */
    protected function checkGetGlobalVariableRelationshipFromGlobals(QMGlobalVariableRelationship $c): void{
        $fromMemoryByName = QMGlobalVariableRelationship::getFromMemoryByCauseAndEffectNameOrId($c->causeVariableName, $c->effectVariableName);
        $this->assertEquals($c->causeVariableName, $fromMemoryByName->causeVariableName);
        $this->assertEquals($c->effectVariableName, $fromMemoryByName->effectVariableName);
        $fromMemoryById = QMGlobalVariableRelationship::getFromMemoryByCauseAndEffectNameOrId($c->causeVariableId, $c->effectVariableId);
        $this->assertEquals($c->causeVariableId, $fromMemoryById->causeVariableId);
        $this->assertEquals($c->effectVariableId, $fromMemoryById->effectVariableId);
    }
}
