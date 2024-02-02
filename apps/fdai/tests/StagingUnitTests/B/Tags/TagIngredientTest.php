<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Tags;
use App\Logging\QMLog;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use Tests\SlimStagingTestCase;
use Throwable;

class TagIngredientTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testIngredientTag(){
        $serving = QMUserVariable::getByNameOrId(230, "Carrots - Raw (serving)");
        $grams = QMUserVariable::getByNameOrId(230, "Carrots Raw");
        QMAuth::loginMike();
        try {
            $response = QMUserTag::addIngredientUserTag([], $grams->getVariableIdAttribute(), $serving->getVariableIdAttribute(), 100);
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
        $gramTaggedVariables = $grams->getCommonAndUserTaggedVariables();
        $servingTaggedVariables = $serving->getCommonAndUserTaggedVariables();
        $this->assertGreaterThan(0, count($gramTaggedVariables));
        $cv = $grams->getCommonVariable();
        $calculated = $cv->calculateNumberCommonTaggedBy();
        $this->assertGreaterThan(0, $calculated);
        $tagged = $grams->getCommonAndUserTaggedVariables();
        $this->assertGreaterThan(0, count($tagged));
        $gramsMeasurements = $grams->getQMMeasurements();
        $gramsMeasurementsWithTags = $grams->getMeasurementsWithTags();
        $this->assertGreaterThan(count($gramsMeasurements), count($gramsMeasurementsWithTags));
        try {
            QMUserTag::addIngredientUserTag([], $serving->getVariableIdAttribute(), $grams->getVariableIdAttribute(), 0.01);
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
        $raw = $grams->getQMMeasurements();
        $tags = $grams->getMeasurementsWithTags();
        $this->assertGreaterThan(count($raw), count($tags));
    }
}
