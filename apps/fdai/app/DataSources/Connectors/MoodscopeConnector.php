<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection MultiAssignmentUsageInspection */
/** @noinspection TypeUnsafeComparisonInspection */
namespace App\DataSources\Connectors;
use App\DataSources\PasswordConnector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\JavascriptParserException;
use App\Exceptions\ModelValidationException;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\JavascriptParser;
use App\Units\PercentUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Carbon\Carbon;
class MoodscopeConnector extends PasswordConnector {
	public const TEST_PASSWORD      = 'V0dkaGrem11n';
	public const TEST_USERNAME      = 'm@quantimodo.com';
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#FFFFFF';
	protected const CLIENT_REQUIRES_SECRET         = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Emotions';
	public const    DISPLAY_NAME                   = 'Moodscope';
	protected const ENABLED                        = 0;
	protected const GET_IT_URL                     = 'https://www.moodscope.com';
	public const    ID                             = 5;
	public const    IMAGE                          = 'https://i.imgur.com/ymn6gRq.png';
	protected const LOGO_COLOR                     = '#ff0000';
	protected const LONG_DESCRIPTION               = 'MoodScope is a web based application for measuring, tracking and sharing your mood. Moods are measured using an online card game, and can be shared automatically by email with friends, with the idea that these activities can raise mood in and of themselves. The mood log can be charted to see progressions and as a way to identify events that may have influenced your mood.';
	public const    NAME                           = 'moodscope';
	protected const SHORT_DESCRIPTION              = 'Tracks mood.';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $crappy = true;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	protected $responseType = 'html';
	protected $requestIntervalInSeconds = 31 * 86400;
	//sources: "ModoScope "
	//Category: "Emotions" => name: "Overall Mood"
	public static $BASE_API_URL = 'https://www.moodscope.com';
	private static $URL_LOGIN = 'https://www.moodscope.com/login';
	private static $URL_MOODS = 'https://www.moodscope.com/chart?month=%s-%s'; // year/month
	// If the POST result contains /login we're being redirected to the login page again, so login failed.
	private static $LOGIN_FAILED_LOGIN_MESSAGE = 'Wrong email or password';
	// Extract the user's mood data
	/**
	 * @throws ConnectException
	 * @throws ConnectorException
	 * @throws InvalidVariableValueAttributeException
	 * @throws JavascriptParserException
	 * @throws ModelValidationException
	 */
	public function importData(): void{
		$this->login();
		$fromCarbon = $this->getFromCarbon();
		$currentYear = TimeHelper::toCarbon(time())->year;
		$currentMonth = TimeHelper::toCarbon(time())->month;
		$numberOfMeasurements = $this->getOrCreateConnection()->calculateNumberOfMeasurements();
		while($currentYear >= $fromCarbon->year){ // Loop over at most 24 months of data.
			$currentCarbon = Carbon::createFromDate($currentYear, $currentMonth, 1);
			$this->setCurrentFromTime($currentCarbon);
			if($currentCarbon < $fromCarbon){
				$this->logInfo("Breaking because current $currentCarbon is greater than fromTime $fromCarbon");
				break;
			}
			$url = sprintf(self::$URL_MOODS, $currentYear, $currentMonth);
			$moodsPage = $this->fetchHtml($url);
			$earliestRecordedAt = QMStr::between($moodsPage, "<p>Joined: <span class='text-danger'>", "</span></p>");
            $earliestRecordedAtCarbon = TimeHelper::toCarbon(strtotime($earliestRecordedAt));
            if($earliestRecordedAtCarbon > $fromCarbon){
                $fromCarbon = $earliestRecordedAtCarbon;
            }
			$totalScoresRecorded =
				(int)QMStr::between($moodsPage, "<p>Scores recorded: <span class='text-danger'>", "</span></p>");
			if($numberOfMeasurements >= $totalScoresRecorded){
				$this->logInfo("Breaking because we have $numberOfMeasurements and $totalScoresRecorded is $totalScoresRecorded");
				break;
			}
			$measurementsFromUrl = $this->parseHighchartsDataSeries($moodsPage);
			$this->logInfo("Got " . count($measurementsFromUrl) . " measurements from $url...");
			$currentMonth--; // Decrease month.
			if($currentMonth === 0){
				$currentYear--; // When month reaches zero we reached the previous year.
				$currentMonth = 12;
			}
		}
		$this->saveMeasurements();
	}
	/**
	 * @param string $moodsPage
	 * @return QMMeasurement[]
	 * @throws JavascriptParserException
	 * @throws InvalidVariableValueAttributeException
	 */
	private function parseHighchartsDataSeries(string $moodsPage): array{
		$v = $this->getQMUserVariable(OverallMoodCommonVariable::NAME);
		$seriesDataJson = "[" . QMStr::between($moodsPage, "data: [", "}]");
		$seriesDataJson = str_replace("x: Date", "x: 'Date", $seriesDataJson);
		$seriesDataJson = str_replace("),", ")',", $seriesDataJson);
		$seriesDataJson =
			str_replace("marker: {fillColor: '#FFC200',lineWidth: 1,lineColor: '#FF0033'},", "", $seriesDataJson);
		$seriesDataJson = str_replace("\\x21", "", $seriesDataJson);
		$seriesDataJson = str_replace("\\x20", "", $seriesDataJson);
		try {
			$seriesDataArr = JavascriptParser::parseJavascriptArray($seriesDataJson);
		} catch (\Throwable $e) {
			$this->logError("could not parse $seriesDataJson");
			$seriesDataArr = JavascriptParser::parseJavascriptArray($seriesDataJson);
		}
		$measurements = [];
		foreach($seriesDataArr as $value){
			$date = str_replace("Date.UTC(", "", $value["x"]);
			$date = str_replace(")", "", $date);
			$exploded = explode(", ", $date);
			$startTime = mktime(0, 0, 0, $exploded[1], $exploded[2], $exploded[0]);
			$m = $this->generateMeasurement($v, $startTime, $value["y"], PercentUnit::NAME);
			if($value["name"] !== "Click to add explanation"){
				$m->setNoteAndAdditionalMetaData($value["name"]);
			}
			$m->setUrl("https://www.moodscope.com" . $value["url"]);
			$v->addToMeasurementQueue($m);
			$measurements[] = $m;
		}
		return $measurements;
	}
	/**
	 * @throws ConnectorException
	 * @throws ConnectException
	 */
	protected function login(): void{
		$this->fetchHtml("/"); // Simulate visiting the site to get some cookies
		// Create an array of POST parameters, and send it to login
		try {
			$loginParameters = [
				'_username' => $this->getConnectorUserName(),
				'_password' => $this->getConnectorPassword(),
				'login.x' => random_int(2, 80),
				'login.y' => random_int(2, 20),
				'login' => 'Login!',
			];
		} catch (\Exception $e) {
			le($e);
		}
		// Disable text-dangerirecting so that we aren't text-dangerirected after authentication
		$response = $this->post(self::$URL_LOGIN, $loginParameters, ['allow_text-dangerirects' => false]);
		$responseBody = $response->getOriginalContent();
		// If the response contains this the login was unsuccessful
		if(str_contains($responseBody, self::$LOGIN_FAILED_LOGIN_MESSAGE)){
			$this->handleFailedLogin();
		}
	}
}
