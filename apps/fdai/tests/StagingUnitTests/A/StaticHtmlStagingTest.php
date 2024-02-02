<?php /** @noinspection PhpUnitAssertEqualsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A;
use App\Exceptions\InvalidStringException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Properties\Base\BaseNameProperty;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Properties\Variable\VariableMaximumRecordedValueProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Utils\EnvOverride;
use App\Utils\SecretHelper;
use App\VariableCategories\ConditionsVariableCategory;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\PrideCommonVariable;
use App\Variables\CommonVariables\FoodsCommonVariables\RawSpinachByWeightCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\DiltiazemCommonVariable;
use Tests\SlimStagingTestCase;
class StaticHtmlStagingTest extends SlimStagingTestCase
{
    public function testKeywords(){
        // TODO: Prevent `Raw Spinach by` crap
        $v = RawSpinachByWeightCommonVariable::instance();
        $calculated = VariableSynonymsProperty::calculate($v);
        $this->assertArrayEquals([
            0 => 'Spinach Raw',
            1 => 'Raw Spinach by Weight',
            2 => 'Raw Spinach',
            3 => 'Raw Spinach by',], $calculated);
        $keywords = $v->getKeyWordString();
        $this->assertEquals('Spinach Raw, Raw Spinach by Weight, Raw Spinach, Raw Spinach by, Foods', $keywords);
        $this->assertArrayEquals([
            0 => 'Spinach Raw',
            1 => 'Raw Spinach by Weight',
            2 => 'Raw Spinach',
            3 => 'Raw Spinach by',], $v->getSynonymsAttribute());
    }
    public function testVariableShowPage(){
        //$this->skipTest("Changes too much");
        //QMProfile::startProfile();
        //GlobalVariableRelationshipIsPublicProperty::updateAll();
        //VariableIsPublicProperty::assertNoInvalidRecords();
        $v = PrideCommonVariable::instance();
        $max = $v->calculateAttribute(VariableMaximumRecordedValueProperty::NAME);
        $this->assertEquals(5, $max);
        $this->assertFalse(EmotionsVariableCategory::PREDICTOR, "Why would we want this null?");
        $this->assertFalse($v->isPredictor(), "Why would we want this null?");
        $this->assertGreaterThan(73, $v->publicOutcomes()->count(), "publicOutcomes");
        $this->assertGreaterThan(790, $v->publicPredictors()->count(), "publicPredictors");
        $this->compareVariableShowPage($v->getId());
    }
	public function testOutcomeVariableShowPage(){
		$v = DiltiazemCommonVariable::instance();
		$this->assertTrue($v->isPredictor(), "Why would we want this null?");
		$this->assertGreaterThan(9, $v->publicOutcomes()->count(), "publicOutcomes");
		$this->assertEquals(0, $v->publicPredictors()->count(), "publicPredictors");
		$this->compareVariableShowPage($v->getId());
	}
    public function testInterestingCategories(){
        $this->assertNames(array (
            0 => 'Emotions',
            1 => 'Physique',
            2 => 'Physical Activity',
            3 => 'Sleep',
            4 => 'Social Interactions',
            5 => 'Vital Signs',
            6 => 'Cognitive Performance',
            7 => 'Symptoms',
            8 => 'Nutrients',
            9 => 'Goals',
            10 => 'Treatments',
            11 => 'Activities',
            12 => 'Foods',
            13 => 'Conditions',
            14 => 'Environment',
            15 => 'Causes of Illness',
        ), VariableCategory::getInterestingCategories());
    }
    public function testBoringCategories(){
        $this->assertNames(array (
            0 => 'Locations',
            1 => 'Miscellaneous',
            2 => 'Books',
            3 => 'Software',
            4 => 'Payments',
            5 => 'Movies and TV',
            6 => 'Music',
            7 => 'Electronics',
            8 => 'IT Metrics',
            9 => 'Economic Indicators',
            10 => 'Investment Strategies',
        ), VariableCategory::getBoringVariableCategories());
    }
    public function testVariablesIndexPage(){
        $this->skipTest("Changes too much");
        if(EnvOverride::isLocal()){
            FileHelper::writeToPublic('index.html', Variable::getIndexPageHtml());
        }
        $this->generateIndexPageForClass(Variable::class, false);
    }
    public function testConditionsCategoryPage(){
        $this->compareShowPage(VariableCategory::find(ConditionsVariableCategory::ID), false);
    }
    public function testMigrainesPage(){
        $this->compareShowPage(Variable::findByNameOrId("Migraine"), false);
    }
    public function testVariableCategoryPages(){
        $this->skipTest("Changes too much");
        //VariableIsPublicProperty::updateAll();
        $invalid = VariableIsPublicProperty::whereInvalid()->get();
        $this->assertCount(0, $invalid,
            print_r(VariableNameProperty::pluckNames($invalid), true));
        $this->generateIndexPageForClass(VariableCategory::class, false);
        $this->generateShowPagesForClass(VariableCategory::class, true);
    }
    /**
     * @param string|BaseModel $class
     * @param bool $ignoreNumbers
     * @throws InvalidStringException
     */
    public function generateIndexPageForClass(string $class, bool $ignoreNumbers): void{
        $this->compareStaticHtml($class::getIndexFilePath(), $class::getIndexPageHtml(), $ignoreNumbers);
    }
    /**
     * @param string|BaseModel $class
     * @param bool $ignoreNumbers
     */
    protected function generateShowPagesForClass(string $class, bool $ignoreNumbers): void{
        $models = $class::getIndexModels();
        foreach($models as $model){
            $this->compareShowPage($model, $ignoreNumbers);
        }
    }
    public function testEmotionIndexVariables(){
        VariableIsPublicProperty::fixInvalidRecords();
        $qb = EmotionsVariableCategory::instance()->indexVariablesQB();
        $actual = BaseNameProperty::pluckArray( $qb->get());
        $this->compareObjectFixture(__FUNCTION__, $actual);
    }
    public function testVariablesIndexVariables(){
        $actual = BaseNameProperty::pluckArray(Variable::getIndexModels());
        sort($actual);  // Changes too much otherwise
        $this->compareObjectFixture(__FUNCTION__, $actual);
    }
    /**
     * @param BaseModel $model
     * @param bool $ignoreNumbers
     * @throws InvalidStringException
     */
    protected function compareShowPage($model, bool $ignoreNumbers): void{
        $path = $model->getShowPublicIndexFilePath();
        $this->compareShowJS($model, $ignoreNumbers);
        try {
            $this->compareStaticHtml($path, $model->getShowPageHtml(), $ignoreNumbers);
        } catch (\Throwable $e) {
            le($e); // Comment this line to debug
            QMLog::info(__METHOD__.": ".$e->getMessage());
            $this->compareStaticHtml($path, $model->getShowPageHtml(), $ignoreNumbers);
        }
    }
    /**
     * @param BaseModel $model
     * @param bool $ignoreNumbers
     */
    protected function compareShowJS($model, bool $ignoreNumbers): void{
        $js = $model->getShowJs();
        VariableNameProperty::assertDoesNotContainPrivateNames($js);
        //VariableNameProperty::assertDoesNotContainStupidNames($js);
        SecretHelper::assertDoesNotContainSecrets($js, __FUNCTION__);
        $this->assertFalse(FileHelper::fileExists($model->getShowPublicJsPath()),
            "We shouldn't save stuff in public folder because the existence of a variables folders interferes with variable requests.  Just use static folder instead. ");
        $this->compareFile($model->getShowStaticJsPath(), $js, $ignoreNumbers);
    }
    /**
     * @param string|int $nameOrId
     * @throws InvalidStringException
     */
    protected function compareVariableShowPage($nameOrId): void{
        $v = Variable::findByNameOrId($nameOrId);
        $this->compareShowPage($v->l(), false);
    }
}
