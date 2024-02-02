<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\Correlations\UserVariableRelationships;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Models\UserVariableRelationship;
use App\Variables\CommonVariables\EnvironmentCommonVariables\BarometricPressureCommonVariable;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class UserVariableRelationshipValidationTest extends SlimStagingTestCase
{
    public function testInvalidValuePredictingHighOutcome(): void{
        $c = UserVariableRelationship::whereCauseVariableId(BarometricPressureCommonVariable::ID)->first();
        //$c->analyze(__METHOD__);
        $c->setAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME, $c->value_predicting_high_outcome);
        $c->validateAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
        $prop = $c->getPropertyModel(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
        $this->assertNotNull($prop);
        $prop->processAndSetDBValue(1);
        try {
            $prop->validate();
            $this->assertFalse(true, "validation should have failed");
        } catch (InvalidAttributeException $e) {
            $this->assertContains("predicting", $e->getMessage());
        }
        $db = $c->getDBModel();
        try {
            QMBaseTestCase::setExpectedRequestException(ModelValidationException::class);
            $db->setAvgDailyValuePredictingHighOutcome(1);
            $db->validate();
            $this->assertFalse(true, "validation should have failed");
        } catch (ModelValidationException $e) {
            $this->assertContains("predicting", $e->getMessage());
        }
        try {
            $db->validate();
            $this->assertFalse(true, "validation should have failed");
        } catch (ModelValidationException $e) {
            $this->assertContains("predicting", $e->getMessage());
        }
        try {
            $db->save();
            $this->assertFalse(true, "validation should have failed");
        } catch (ModelValidationException $e) {
            $this->assertContains("predicting", $e->getMessage());
        }
    }
}
