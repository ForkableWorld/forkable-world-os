<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Tags;
use App\Logging\QMLog;
use App\Slim\Middleware\QMAuth;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class TagParentCategoryTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testParentCategoryTag(){
        $therapeutic = QMUserVariable::getByNameOrId(230, "Therapeutic M Multivitamin");
        $multivitamins = QMUserVariable::getByNameOrId(230, "Multivitamins");
        $parentId = $multivitamins->getVariableIdAttribute();
        $childId = $therapeutic->getVariableIdAttribute();
        QMAuth::loginMike();
        try {
            QMUserTag::addUserTagByVariableIds(230, $parentId,
                $childId, 1);
        } catch (\Throwable $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
        $this->checkChildVariable($therapeutic);
        $this->checkParentVariable($multivitamins);
        $this->checkChildParentMeasurements($therapeutic, $multivitamins);
    }
}
