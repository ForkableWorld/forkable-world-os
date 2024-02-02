<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\UnitTests\Products;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\Connectors\AmazonConnector;
use App\DataSources\Connectors\GmailConnector;
use App\Models\TrackingReminder;
use App\Products\AmazonHelper;
use App\Products\ProductHelper;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Storage\DB\TestDB;
use App\Storage\Memory;
use App\Units\CountUnit;
use App\Units\PoundsUnit;
use App\Units\ServingUnit;
use App\Utils\AppMode;
use App\VariableCategories\ElectronicsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Tests\UnitTestCase;

class AmazonTest extends UnitTestCase {

    public const DISABLED_UNTIL = "2023-04-01";
    public const REASON_FOR_SKIPPING = 'Waiting for Amazon to approve use of US product API at https://affiliate-program.amazon.com/assoc_credentials/home';
    public const BANANA_NAME = "Bananas";
    public function testUpcLookup(){
        if($this->weShouldSkip()){return;}
        Memory::resetClearOrDeleteAll();
        $upc = '029537049023';
        $name = "Nature's Bounty Lutein";
        $product = ProductHelper::getByUpc($upc);
        $this->assertNotNull($product);
        $paymentVariable = $product->getCommonPaymentVariable();
        $this->assertStringStartsWith(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX, $paymentVariable->displayName);
        $this->assertFalse(stripos($paymentVariable->displayName, VariableNameProperty::PAYMENT_VARIABLE_NAME_SUFFIX));
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals($name, $variable->name);
        $variable->deleteCommonVariableAndAllAssociatedRecords("testing", true);
        $variables = QMVariable::getCommonOrUserVariablesFromUpc($upc);
        $this->assertCount(2, $variables);
    }
    public function testReminderForProbiotics(){
        if($this->weShouldSkip()){return;}
        QMUserVariable::truncate();
        QMTrackingReminder::truncate();
        $userVariable = QMUserVariable::findOrCreateWithReminderFromAmazon(
            GmailConnector::TEST_VARIABLE, 1, null);
        $reminders = $userVariable->getQMTrackingReminders();
        $this->assertCount(1, $reminders);
    }
    public function testSavePurchaseMeasurementAndGetBySynonym(){
        if($this->weShouldSkip()){return;}
        $name = "Nova Nutritions Acetyl L-Carnitine 500 mg 120 Vcaps";
        $product = AmazonHelper::getByKeyword($name);
        $paymentVariable = $product->getCommonPaymentVariable();
        $synonyms = $paymentVariable->getSynonymsAttribute();
        $this->assertContains(VariableNameProperty::toSpending($name), $synonyms);
    }
    public function testAmazonVariableCategory(){
        if($this->weShouldSkip()){return;}
        $product = ProductHelper::getByKeyword("Snore Reducing Aids");
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals(TreatmentsVariableCategory::NAME, $variable->getVariableCategoryName());
        $this->assertTrue($variable->isPublic);
        $this->assertEquals(CountUnit::NAME, $variable->getUserOrCommonUnit()->name);
        $this->assertNotNull($variable->productUrl);
        $this->assertNotNull($variable->description);
        $this->assertTrue($variable->inSynonyms("Snore Reducing Aids"));
        $this->makeSureAllUserVariableUnitIdsAreNull();
    }
    public function testBananasVariableName(){
        if($this->weShouldSkip()){return;}
        $unit = QMUnit::getUnitFromString("Fresh Organic Bananas Approximately 3 Lbs 1 Bunch of 6-9 Bananas");
        $this->assertEquals(PoundsUnit::NAME, $unit->name);
        $product = ProductHelper::getByKeyword(self::BANANA_NAME);
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals(FoodsVariableCategory::NAME, $variable->getVariableCategoryName());
        $this->assertTrue($variable->isPublic);
        $name = $variable->getVariableName();
        if(stripos($name, "Thai") !== false){
            $this->assertEquals("Fresh Thai Bananas", $name);
        } else {
            $this->assertNotFalse(stripos($name, "Banana"));
        }
        //$this->assertEquals("Yellow Silly Bananas Like Runts", $variable->getVariableName());
        $this->assertEquals(ServingUnit::NAME, $variable->getCommonUnit()->name);
        $this->assertNotNull($variable->productUrl);
        $this->assertNotNull($variable->description);
        //$this->assertTrue($variable->synonymsContain("Fresh Organic Bananas Approximately 3 Lbs 1 Bunch of 6-9 Bananas"));
        $this->assertTrue($variable->inSynonyms(self::BANANA_NAME));
        $this->makeSureAllUserVariableUnitIdsAreNull();
    }
    public function testCreateReminder(){
        TestDB::deleteUserData();
        $variableName = "Wild Bill Vape Juice 75/25 VG/PG 0.6% Nicotine";
        QMCommonVariable::setGetAmazonProductForNewVariables(true);
        $this->setAuthenticatedUser(1);
        AppMode::setIsApiRequest( false);
        TrackingReminder::fromData([
            'unitAbbreviatedName'  => CountUnit::NAME,
            'variableName' => $variableName,
            'variableCategoryName' => TreatmentsVariableCategory::NAME,
            'userId' => 1,
            TrackingReminder::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $reminders = QMTrackingReminder::getTrackingReminders($this->getOrSetAuthenticatedUser(1));
        $this->assertCount(1, $reminders);
        foreach ($reminders as $reminder){
            $this->assertEquals($variableName, $reminder->variableName);
        }
    }
    public function testListerineVariableName(){
        if($this->weShouldSkip()){return;}
        $product = ProductHelper::getByKeyword(
            "Freshburst Listerine Antiseptic Mouthwash Kills Germs Causing Bad Breath, 500 ml");
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals(TreatmentsVariableCategory::NAME, $variable->getVariableCategoryName());
        $variableName = $variable->getVariableName();
        $this->assertContains($variableName, [
            "Listerine Freshburst Antiseptic Mouthwash",
            "Freshburst Listerine Antiseptic Mouthwash",
            "Listerine Freshburst Antiseptic Mouthwash For Bad Breath",
            "Freshburst Listerine Antiseptic Mouthwash Fresh Burst"
        ]);
        $this->assertEquals(CountUnit::NAME, $variable->getCommonUnit()->name);
        $this->assertNotNull($variable->productUrl);
        $this->assertNotNull($variable->description);
        $this->assertTrue($variable->inSynonyms("Freshburst Listerine Antiseptic Mouthwash Kills Germs Causing Bad Breath, 500 ml"));
        $userVariable = $variable->getOrCreateUserVariable(1);
        $userVariable->createTrackingReminder();
        $this->assertWeHaveACountReminder($userVariable);
    }
    public function testDefaultReminderValue(){
        if($this->weShouldSkip()){return;}
        $variableName = "Nature Made Super B Complex Tablets";
        $product = ProductHelper::getByKeyword($variableName);
        $variable = $product->getQMCommonVariableWithActualProductName();
        $this->assertEquals(TreatmentsVariableCategory::NAME, $variable->getVariableCategoryName());
        if(strpos($variable->getVariableName(), "-") !== false){
            $this->assertEquals("Nature Made Super B-Complex", $variable->getVariableName());
        } else {
            $this->assertSame(stripos($variable->getVariableName(), "Nature Made Super B Complex"), 0);
        }
        $this->assertEquals(CountUnit::NAME, $variable->getUserOrCommonUnit()->name);
        $this->assertNotNull($variable->productUrl);
        $this->assertNotNull($variable->description);
        $userVariable = $variable->getExistingNonPaymentUserVariableOrCreateNewWithReminderFromAmazonVariableParams(1);
        $this->assertWeHaveACountReminder($userVariable);
    }
    public function testApostropheVariableName(){
        if($this->weShouldSkip()){return;}
        $product = ProductHelper::getByKeyword("D'Addario EJ16-3D Phosphor Bronze Light Acoustic Guitar Strings Single-Pack");
        $variable = $product->getQMCommonVariableWithActualProductName();
        $commonVariable = QMCommonVariable::findByNameIdOrSynonym(
            "D'Addario EJ16-3D Phosphor Bronze Light Acoustic Guitar Strings Single-Pack", []);
        $this->assertNotNull($commonVariable);
        $this->assertEquals(ElectronicsVariableCategory::NAME, $variable->getVariableCategoryName());
        if(strpos($variable->getVariableName(), "3D") !== false){
            $this->assertNotFalse(stripos($variable->getVariableName(), "Addario EJ16-3D Phosphor Bronze Acoustic Guitar Strings"));
        } else {
            $this->assertEquals("D'Addario EJ16 Phosphor Bronze Light Acoustic Strings", $variable->getVariableName());
        }
        $this->assertEquals(CountUnit::NAME, $variable->getUserOrCommonUnit()->name);
        $this->assertNotNull($variable->productUrl);
        $this->assertNotNull($variable->description);
        $this->assertEquals(AmazonConnector::NAME, $variable->getClientId());
        $this->assertTrue($variable->inSynonyms("D'Addario EJ16-3D Phosphor Bronze Light Acoustic Guitar Strings Single-Pack"));
    }
    public function testBananasStudy(){
        if($this->weShouldSkip()){return;}
        TestDB::deleteUserData();
        $this->createBananaMeasurements();
        $this->createMoodMeasurements();
        $this->getOrSetAuthenticatedUser(1)->analyzeFully(__METHOD__);
        $rows = QMUserVariableRelationship::readonly()->getArray();
        $this->assertCount(3, $rows);
        $correlations = $this->getOrSetAuthenticatedUser(1)->setAllUserVariableRelationships();
        $this->assertCount(3, $correlations);
        $this->makeSureAllUserVariableUnitIdsAreNull();
    }
    protected function createBananaMeasurements(){
        $product = ProductHelper::getByKeyword(self::BANANA_NAME);
        $variable = $product->getUserVariable(1);
        $variable->addToMeasurementQueue(new QMMeasurement($this->getTimeMinusXDays(120), 1));
        $variable->addToMeasurementQueue(new QMMeasurement($this->getTimeMinusXDays(60), 1));
        $variable->saveMeasurements();
        $this->checkBananaVariable();
        $this->checkBananaParentVariable();
    }
    /**
     */
    protected function checkBananaParentVariable(){
        //$bananasPlantains = UserVariable::getByNameOrIdIncludingGlobals(1, "Hard Candy");
        $bananasPlantains = QMUserVariable::getByNameOrId(1, "Bananas & Plantains");
        $this->assertNotNull($bananasPlantains);
        $numberOfMeasurements = $bananasPlantains->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
        $this->assertEquals(2, $numberOfMeasurements);
        $ProcessedMeasurementsWithTagsJoinsChildrenInCommonUnit =
            $bananasPlantains->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertCount(61, $ProcessedMeasurementsWithTagsJoinsChildrenInCommonUnit);
        $this->assertEquals(UserVariableStatusProperty::STATUS_CORRELATE, $bananasPlantains->status);
        $this->assertEquals(2, $bananasPlantains->getNumberOfRawMeasurementsWithTagsJoinsChildren());
    }
    protected function checkBananaVariable(){
        $bananas = QMUserVariable::findUserVariableByNameIdOrSynonym(1, self::BANANA_NAME);
        $UserTagged = $bananas->getUserTaggedVariables();
        $this->assertCount(0, $UserTagged);
        $UserTag = $bananas->getUserTagVariables();
        $this->assertCount(0, $UserTag);
        $CommonTagged = $bananas->getCommonTaggedVariables();
        $this->assertCount(0, $CommonTagged);
        $CommonTag = $bananas->getCommonTagVariables();
        $this->assertCount(2, $CommonTag, "No tags!");
        $bananas->analyzeFully(__FUNCTION__);
        $numberOfMeasurements = $bananas->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
        $this->assertEquals(2, $numberOfMeasurements);
        $numberOfProcessedDailyMeasurements = $bananas->getOrCalculateNumberOfProcessedDailyMeasurementsWithTagsJoinsChildren();
        $this->assertEquals(61, $numberOfProcessedDailyMeasurements);
        $this->assertEquals(UserVariableStatusProperty::STATUS_CORRELATE, $bananas->status);
    }
    protected function createMoodMeasurements(int $userId = 1): array {
        $mood = $this->getMoodQMUserVariable();
        $measurements = parent::createMoodMeasurements();
        $mood->analyzeFully(__FUNCTION__);
        $this->assertEquals(UserVariableStatusProperty::STATUS_CORRELATE, $mood->status);
        return $measurements;
    }
    /**
     * @param QMUserVariable $userVariable
     */
    private function assertWeHaveACountReminder(QMUserVariable $userVariable): void{
        $reminders = $userVariable->getQMTrackingReminders();
        $this->assertEquals(CountUnit::NAME, $reminders[0]->getUserOrCommonUnit()->name);
        $actionArray = $reminders[0]->getNotificationActionButtons();
        $this->assertCount(3, $actionArray);
        $this->assertEquals(1, $actionArray[0]->modifiedValue);
        $this->assertEquals(0, $actionArray[1]->modifiedValue);
    }
}
