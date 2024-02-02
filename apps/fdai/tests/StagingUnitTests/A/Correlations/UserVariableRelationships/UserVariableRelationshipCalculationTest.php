<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Correlations\UserVariableRelationships;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\PhpUnitJobs\Cleanup\UserVariableCleanupJobTest;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class UserVariableRelationshipCalculationTest extends SlimStagingTestCase {
    public function testUserVariableRelationshipCalculation() {
        UserVariableCleanupJobTest::fixExperimentEndTimes();
        $userId = 230;
        $cause = QMUserVariable::getByNameOrId($userId, "Sleep Quality");
        $raw = $cause->getQMMeasurements();
        $this->assertGreaterThan(1, count($raw));
        $processed = $cause->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertGreaterThan(1, count($processed));
        $effect = QMUserVariable::getByNameOrId($userId, "Headache Severity");
        $this->assertNotNull($cause->earliestTaggedMeasurementTime);
        $this->assertNotNull($effect->earliestTaggedMeasurementTime);
        $this->assertNotNull($cause->latestTaggedMeasurementTime);
        $this->assertNotNull($effect->latestTaggedMeasurementTime);
        $fields = QMUserVariableRelationship::getColumns();
        UserVariableRelationship::whereId(0)->forceDelete();
        $this->assertContains(UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT, $fields);
        $this->assertContains(UserVariableRelationship::FIELD_STATUS, $fields);
        $c = QMUserVariableRelationship::getOrCreateUserVariableRelationship($userId, $cause->variableId, $effect->variableId);
        $this->assertTrue($c->uniqueFieldsAreSet());
        $c = new QMUserVariableRelationship(null, $cause, $effect);
        $c->analyzeFully(__FUNCTION__);
        $this->assertGreaterThan(0.1, $c->correlationCoefficient);
        $row = UserVariableRelationship::whereId($c->id)->first();
        $this->assertNotEmpty($row->correlations_over_delays);
        $this->assertNotEmpty($row->correlations_over_durations);
    }
}
