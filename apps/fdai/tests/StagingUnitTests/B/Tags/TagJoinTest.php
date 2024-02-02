<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Tags;
use App\Exceptions\NotFoundException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Slim\Middleware\QMAuth;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
use Throwable;

class TagJoinTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testJoinMagnesiumTag(){
        $this->createTagAndCheckJoinVariables("Life Extension Magnesium 500mg", "Life Extension Magnesium");
    }
    public function testJoinApplesTag(){
        $this->createTagAndCheckJoinVariables("Apples - Raw, With Skin", "Apple");
    }
	/**
	 * @group Production
	 * @param string $aName
	 * @param string $bName
	 * @throws UserVariableNotFoundException
	 */
    public function createTagAndCheckJoinVariables(string $aName, string $bName) {
        $mg = QMUserVariable::getByNameOrId(230, $aName);
        $noMg = QMUserVariable::getByNameOrId(230, $bName);
        QMAuth::loginMike();
        try {
            QMUserTag::deleteUserJoin(230, $mg->getVariableIdAttribute(), $noMg->getVariableIdAttribute(), "testing");
        } catch (NotFoundException $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
            //throw $e;
        }
        try {
            QMUserTag::createJoinTag([
                'parentVariableId' => $mg->getVariableIdAttribute(),
                'joinedVariableId' => $noMg->getVariableIdAttribute()
            ]);
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
            le($e);
        }
        $this->checkJoinedVariables($mg, $noMg);
    }
}
