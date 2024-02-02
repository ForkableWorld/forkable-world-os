<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Types;
use App\Files\FileFinder;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Storage\QueryBuilderHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\MetaHtml;
use App\Units\CountUnit;
use App\Units\GramsUnit;
use App\Units\TabletsUnit;
use App\Utils\UrlHelper;
use App\Variables\CommonVariables\EnvironmentCommonVariables\UVIndexCommonVariable;
use Tests\TestGenerators\ApiTestFile;
use Tests\UnitTestCase;

/**
 * Class StringHelperTest
 * @package Tests\Api\Model
 */
class StringHelperTest extends UnitTestCase {
    public function testScreamingSnakeCase(){
        $res = QMStr::toScreamingSnakeCase("objective-c");
        $this->assertEquals("OBJECTIVE_C", $res);
    }
    public function testInArrayCaseInsensitive(){
        $this->assertTrue(QMArr::inArrayCaseInsensitive(
            "Spending On Amelia Spending",
            ["Spending on Amelia Spending"
        ]));
    }
    public function testSanitizeFilePath(){
        $input = "https://tigerview.ecusd7.org/HomeAccess/Home/WeekView?startDate=10%2F09%2F2020+00%3A00%3A00";
        $output = QMStr::sanitizeFilePath($input);
        $this->assertEquals("tigerview.ecusd7.org/HomeAccess/Home/WeekView-startDate=10-09-2020+00-00-00", $output);
    }
    public function testFilePathToShortClassName(){
        $files = FileFinder::listFiles('app/Models');
        foreach($files as $file){
            $short = QMStr::filePathToShortClassName($file);
            $this->assertNotContains('/', $short);
            $class = '\App\Models\\'.$short;
            $this->assertTrue(class_exists($class), "$class class does not exist");
        }
    }
    public function testCamelToTitle(){
        $output = QMStr::camelToTitle("Average Predictor Treatment Value");
        $this->assertEquals("Average Predictor Treatment Value", $output);

        $output = QMStr::camelToTitle("accessTokens");
        $this->assertEquals("Access Tokens", $output);
    }
    public function testAddUrlParam(){
        $url = "https://angularjs-embeddable.quantimo.do/?plugin=search-relationships&plugin=search-relationships&apiUrl=staging.quantimo.do&apiUrl=staging.quantimo.do";
        $token ="token";
        $output = UrlHelper::addParam($url, 'quantimodoAccessToken', $token);
        $this->assertEquals('https://angularjs-embeddable.quantimo.do/?plugin=search-relationships&apiUrl=staging.quantimo.do&quantimodoAccessToken=token',
            $output);
    }
    public function testToClassName(){
        $output = QMStr::toClassName("study");
        $this->assertEquals("Study", $output);
        $output = QMStr::toClassName("study_images_images");
        $this->assertEquals("StudyImagesImages", $output);
        $output = QMStr::toClassName("Spending on My Italian Secret");
        $this->assertEquals("SpendingOnMyItalianSecret", $output);
        $output = QMStr::toClassName("_TrackingReminderNotifications");
        $this->assertEquals("TrackingReminderNotifications", $output);
        $n = QMStr::toClassName(UVIndexCommonVariable::NAME);
        $this->assertEquals('UVIndex', $n);
    }
    public function testReplaceNumbersWithWords(){
        $output = QMStr::replaceNumbersWithWords("_TrackingReminderNotifications");
        $this->assertEquals("_TrackingReminderNotifications", $output);
    }
    public function testConvertPathToTestName(){
        $n = ApiTestFile::generateNamePrefix("/api/v3/trackingReminderNotifications?" .
                                             "access_token=" . BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535 .
                                             "&appVersion=2.9.203&clientId=quantimodo&platform=web");
        $this->assertEquals("TrackingReminderNotifications", $n);
    }
    public function testRemoveDatesFromString(){
        $actual = QMStr::removeDatesAndTimes("\"updatedAt\": \"2020-01-31 03:14:07\",");
        $this->assertEquals("\"updatedAt\": \"2020-01-01 00:00:00\",", $actual);
    }
    public function testRemoveUnixtimeFromString(){
        $actual = QMStr::removeUnixTimestampsFromString(time());
        $this->assertEquals("[UNIXTIME REDACTED]", $actual);
    }
    public function testClassToTitle(){
        $n = QMStr::classToPluralTitle("GradeReport");
        $this->assertEquals("Grade Reports", $n);
    }
    public function testSlugify(){
        $n = QMStr::slugify("Daily 5 HTP Distribution");
        $this->assertEquals('daily-5-htp-distribution', $n);
        $n = QMStr::slugify("PushNotificationsJob");
        $this->assertEquals('push-notifications-job', $n);
    }
    public function testGetStringAfterLastSubstring() {
        $n = QMStr::afterLast('/home/vagrant/qm-api/Jobs/Analytics', '/Jobs/');
        $this->assertEquals('Analytics', $n);
        $n = QMStr::afterLast(\App\PhpUnitJobs\Import\ConnectionsJob::class, '\\');
        $this->assertEquals('ConnectionsJob', $n);
    }
    public function testGetStringAfterSubstring() {
        $n = QMStr::after('github.io/', 'https://caolan.github.io/async/');
        $this->assertEquals('async/', $n);
        $n = QMStr::after('=', 'REDIS_DATABASE=0');
        $this->assertEquals('0', $n);
    }
    public function testGetStringBetween(){
        $sql = "insert into `migrations` (`migration`, `batch`) values (?, ?)";
        $t = QMStr::between($sql, "insert into"." ", ' ', null, true);
        $t = str_replace('`', '', $t);
        $t = trim($t);
        $this->assertEquals("migrations", $t);
    }
    public function testSnakize(){
        $n = QMStr::snakize("oAuthClients");
        $this->assertEquals('o_auth_clients', $n);
        $n = QMStr::snakize("Daily 5 HTP Distribution");
        $this->assertEquals('daily_5_htp_distribution', $n);
        $snake = QMStr::snakize('pValue');
        $this->assertEquals('p_value', $snake);
    }
    public function testTitleCase(){
        $name = QMStr::titleCaseSlow("UV Index");
        $this->assertContains("UV Index", $name);

        $name = QMStr::titleCaseSlow("BP");
        $this->assertContains("BP", $name);
    }
    public function testSanitizeVariableName(){
        $name = VariableNameProperty::sanitizeSlow("Pollen Index");
        $this->assertContains("Pollen Index", $name);

        $name = VariableNameProperty::sanitizeSlow("Swollen Feet");
        $this->assertContains("Swollen Feet", $name);

        $name = VariableNameProperty::sanitizeSlow("Daily Step Count");
        $this->assertContains("Daily Step Count", $name);

        $name = VariableNameProperty::sanitizeSlow("UV Index");
        $this->assertContains("UV Index", $name);

        $name = VariableNameProperty::sanitizeSlow("BP");
        $this->assertContains("BP", $name);

        $name = VariableNameProperty::sanitizeSlow("B-Complex With Folic Acid Plus Vitamin C");
        $this->assertContains("B-Complex With Folic Acid Plus Vitamin C", $name);
    }
    /**
     * @group Model
     * @group StringHelper
     */
    public function testFormatAmazonVariableNameWithUnitAndDefaultValueInName() {
        $originalName = "Nature (g)";
        $expectedVariableName = "Nature";
        $this->checkVariableNameUnitAndDefaultValueInference($originalName, $expectedVariableName, GramsUnit::ABBREVIATED_NAME, null);
        $originalName = "Nature Made Super B Complex Tablets";
        $expectedVariableName = "Nature Made Super B Complex";
        $this->checkVariableNameUnitAndDefaultValueInference($originalName, $expectedVariableName, TabletsUnit::ABBREVIATED_NAME, null);
        $unit = QMUnit::getUnitFromString("Ea");
        $this->assertEquals(CountUnit::NAME, $unit->name);
        $originalName = "Sundown Nat Odor Garlic 100 Mg 250 Ea";
        $expectedVariableName = "Sundown Nat Odor Garlic";
        $this->checkVariableNameUnitAndDefaultValueInference($originalName, $expectedVariableName,
            "mg", 100);
        $originalName = "Sundown Nat Odor Garlic  250 Ea 100 Mg";
        $expectedVariableName = "Sundown Nat Odor Garlic";
        $this->checkVariableNameUnitAndDefaultValueInference($originalName, $expectedVariableName,
            "mg", 100);
        $originalName = "Nutricost Psyllium Husk Powder 500 Grams 5g Per Serving";
        $expectedVariableName = "Nutricost Psyllium Husk Powder";
        $this->checkVariableNameUnitAndDefaultValueInference($originalName, $expectedVariableName,
            "g", 5);
    }
    /**
     * @param string $originalName
     * @param string $expectedVariableName
     * @param string $expectedUnitAbbreviatedName
     * @param float $expectedDefaultValue
     */
    public function checkVariableNameUnitAndDefaultValueInference($originalName,
                                                                  $expectedVariableName,
                                                                  $expectedUnitAbbreviatedName,
                                                                  $expectedDefaultValue) {
        $unit = QMUnit::getUnitFromString($originalName);
        $variableName = VariableNameProperty::sanitizeSlow($originalName, $unit);
        $this->assertEquals($expectedVariableName, $variableName);
        $this->assertEquals($expectedUnitAbbreviatedName, $unit->abbreviatedName);
        $defaultValue = QMStr::getDefaultValueFromString($originalName);
        $this->assertEquals($expectedDefaultValue, $defaultValue);
    }
    public function testReplaceBetween(){
        $before = '<li>
                    <a href="http://localhost/datalab/commonTags?common_tags_tag_variable_id=1398&DB_URL=mysql%3A%2F%2Fhomestead%3Asecret%40127.0.0.1%2Fquantimodo_test%3Freconnect%3Dtrue" target=\'_self\' title=\'Common Tags where this is the Tag Variable\'>
                        Common Tags Where Tag Variable <span class="pull-right badge bg-blue">N/A</span>
                    </a>
                </li>

                <li>
                    <a href="http://localhost/datalab/units/10?DB_URL=mysql%3A%2F%2Fhomestead%3Asecret%40127.0.0.1%2Fquantimodo_test%3Freconnect%3Dtrue" target=\'_self\' title=\'Unit of measurement\'>
                        Default Unit: 1 to 5 Rating <span class="pull-right badge bg-blue"></span>
                    </a>
                </li>';
        $after = QMStr::replace_between_and_including($before, 'DB_URL', 'econnect%3Dtrue', '');
        $this->assertEquals('<li>
                    <a href="http://localhost/datalab/commonTags?common_tags_tag_variable_id=1398&" target=\'_self\' title=\'Common Tags where this is the Tag Variable\'>
                        Common Tags Where Tag Variable <span class="pull-right badge bg-blue">N/A</span>
                    </a>
                </li>

                <li>
                    <a href="http://localhost/datalab/units/10?" target=\'_self\' title=\'Unit of measurement\'>
                        Default Unit: 1 to 5 Rating <span class="pull-right badge bg-blue"></span>
                    </a>
                </li>', $after);
    }
    public function testHumanizedWhereClause(){
        $_SERVER['REQUEST_URI'] = '/datalab/user_variable_relationships?cause_variable_id=89305&effect_variable_id=1874&generate_phpunit=1';
        $_GET = array (
            'cause_variable_id' => '89305',
            'effect_variable_id' => '1874',
        );
        $this->assertTrue(QMRequest::isModelRequest());
        $res = QueryBuilderHelper::getHumanizedWhereClause($_GET, UserVariableRelationship::TABLE);
        $this->assertEquals("where the Cause Variable ID is 89305 and where the Effect Variable ID is 1874",
            $res);
        $title = MetaHtml::generateTitle();
        $this->assertEquals("Individual Case Studies where the Cause Variable ID is 89305 and where the Effect Variable ID is 1874",
            $title);
    }
    public function testHumanizeNeverEndedWhereClause(){
        $str = QueryBuilderHelper::whereParamsToHumanString("analysis_ended_at", "user_variable_relationships", "NULL");
        $this->assertEquals("where the Analysis Ended value is not set", $str);
    }
}
