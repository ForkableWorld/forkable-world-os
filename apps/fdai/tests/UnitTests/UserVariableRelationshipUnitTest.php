<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\UserVariableRelationship;
use Tests\UnitTestCase;
class UserVariableRelationshipUnitTest extends UnitTestCase {
    public function testUserVariableRelationshipFields(){
        $fields = QMUserVariableRelationship::getSelectColumns();
        $this->assertContains(UserVariableRelationship::TABLE.".". UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT." as analysisEndedAt", $fields);
        $fields = QMUserVariableRelationship::getColumns();
        $this->assertContains(UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT, $fields);
    }
}
