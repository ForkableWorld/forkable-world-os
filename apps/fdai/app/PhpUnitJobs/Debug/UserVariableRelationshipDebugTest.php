<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\PhpUnitJobs\JobTestCase;
use App\Variables\QMUserVariable;
class UserVariableRelationshipDebugTest extends JobTestCase {
    private const CAUSE = "Outdoor Temperature";
    private const EFFECT = "Overall Mood";
    public function testDebugUserVariableRelationshipAnalysis(){
        $c = QMUserVariableRelationship::getOrCreateUserVariableRelationship(65141, self::CAUSE, self::EFFECT);
        $c->analyzeFully(__FUNCTION__);
    }
    public function testUserVariableRelationshipDebug(){
        $cause = QMUserVariable::getByNameOrId(230, self::CAUSE);
        $effect = QMUserVariable::getByNameOrId(230, self::EFFECT);
        QMUserVariableRelationship::shouldWeCalculate($cause, $effect);
        $c = new QMUserVariableRelationship(null, $cause, $effect);
        $c->analyzeFully(__FUNCTION__);
        $html = $c->getStudyHtml()->getTitleGaugesTagLineHeader(true, true);
        $cause->forceAnalyze(__FUNCTION__);
        $correlations = $cause->getCorrelationsAsCause();
        $rows = QMUserVariableRelationship::readonly()
            ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $cause->getVariableIdAttribute())
            ->getArray();
        $correlations = QMUserVariableRelationship::get([
            UserVariableRelationship::FIELD_USER_ID => 230,
            'causeVariableName'              => self::CAUSE,
            'effectVariableName'             => self::EFFECT,
        ]);
        $c = $correlations[0];
        $val = $c->changeFromBaselineSentence();
        $this->assertNotNull($val);
    }
    public function testOutputNumberOfCorrelationsByUser(){
        $v = QMUserVariable::getByNameOrId(230, "Spending on Restaurants");
        $v->analyzeFully(__FUNCTION__);
    }
}
