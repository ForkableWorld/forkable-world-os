<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection MultiAssignmentUsageInspection */
/** @noinspection TypeUnsafeComparisonInspection */
namespace App\DataSources\Connectors;
use App\DataSources\PasswordConnector;
use App\Exceptions\InvalidTagCategoriesException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\TemporaryImportException;
use App\Exceptions\TooManyMeasurementsException;
use App\Logging\QMLog;
use App\Mail\QMSendgrid;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Reports\GradeReport;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\S3\S3Private;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Units\CountUnit;
use App\Units\PercentUnit;
use App\Units\YesNoUnit;
use App\VariableCategories\BooksVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\Variables\CommonVariables\GoalsCommonVariables\DailyAverageGradeCommonVariable;
use App\Variables\QMUserVariable;
use LogicException;
class TigerViewConnector extends PasswordConnector {
	public const TEST_PASSWORD      = 'tiger1955';
	public const TEST_USERNAME      = 'M.Sinn';
	protected $useFileResponsesInTesting = true;
	protected $allowMeasurementsForCurrentDay = true;
	protected $requestIntervalInSeconds = 7 * 86400;
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#ff8800';
	protected const CLIENT_REQUIRES_SECRET         = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = GoalsVariableCategory::NAME;
	protected const DEVELOPER_CONSOLE              = 'https://tigerview.ecusd7.org';
	protected const DEVELOPER_PASSWORD             = 'tiger1955';
	protected const DEVELOPER_USERNAME             = 'M.Sinn';
	protected const GET_IT_URL                     = 'https://tigerview.ecusd7.org';
	protected const LOGIN_URL                      = 'https://tigerview.ecusd7.org/HomeAccess/Account/LogOn?ReturnUrl=%2fhomeaccess';
	protected const LOGO_COLOR                     = '#ff0000';
	protected const LONG_DESCRIPTION               = 'Web-based parent access system for student academic performance';
	protected const SHORT_DESCRIPTION              = 'Tracks academic performance and student behaviour.';
	protected $responseType = 'html';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $mergeOverlappingMeasurements = true;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const CLASS_ASSIGNMENT_SUFFIX          = " Class Assignment";
	public const CLASS_DAILY_AVERAGE_GRADE_SUFFIX = " Class Daily Average Grade";
	public const CURRENT_AVERAGE_GRADE_PREFIX     = "Current Quarterly Average Grade for ";
	public const DISPLAY_NAME                     = 'TigerView';
	public const ENABLED                          = true;
	public const ID                               = 89;
	public const IMAGE                            = 'https://static.quantimo.do/img/connectors/tigerview.png';
	public const NAME                             = 'tigerview';
	public const TESTING_DISABLED_UNTIL           = "2021-09-01";
	public static $BASE_API_URL = 'https://tigerview.ecusd7.org';
	public const SUBJECTS = [
		"Spanish",
		"Math",
		"Language Arts",
		"Science",
	];
	protected $report;
	protected $assignmentVariables;
	/**
	 * @return void
	 * @throws ConnectorException
	 */
	public function importData(): void{
		$this->login();
		$currentFromTime = $this->getFromTime();
		while($currentFromTime < time()){
			$date = date('m/d/Y', $currentFromTime) . " 00:00:00";
			$arr = $this->getGradesTable($date);
			foreach($arr as $subjectArray){
				$this->addSubjectMeasurements($subjectArray);
			}
			$currentFromTime = $currentFromTime + 7 * 86400;
			if($this->weShouldBreak()){
				break;
			}
		}
		$newMeasurements = $this->saveMeasurements();
		if($newMeasurements){
			$this->email();
		}
	}
	/**
	 * @param string $startDate
	 * @return array
	 * @throws ConnectorException
	 */
	private function getGradesTable(string $startDate): array{
		$url = 'https://tigerview.ecusd7.org/HomeAccess/Home/WeekView?startDate=' . urlencode($startDate);
		$this->logInfo("Getting grades for week of $startDate from $url");
		$responseHtml = $this->getRequest($url);
		if(stripos($responseHtml, '<table') === false){
            $htmlUrl = S3Private::upload("tigerViewHtmlWithoutTable", $responseHtml);
			$this->logError("No table in this HTML from $url: $htmlUrl");
			return [];
		}
		try {
			$arr = HtmlHelper::htmlTableToArray($responseHtml);
		} catch (\Throwable $e) {
			if(stripos($e->getMessage(), "No first header") !== false){
				throw new TemporaryImportException($this, $e->getMessage() . "
                when trying to parse this response from
                $url
                =>
                $responseHtml");
			} else{
				/** @var LogicException $e */
				throw $e;
			}
		}
		return $arr;
	}
	/**
	 * @param $subjectArray
	 */
	private function addSubjectMeasurements(array $subjectArray): void{
		$subject = TigerViewConnector::pluckSubjectName($subjectArray["Class"]);
		if($subject === "Activity"){
			return;
		}
		$this->addCurrentAverageMeasurement($subject, $subjectArray["Current Average"]);
		foreach($subjectArray as $columnHeaderString => $cellHtml){
			if(stripos($columnHeaderString, 'day') !== false){
				$date = TigerViewConnector::getDate($columnHeaderString);
				if(strtotime($date) < $this->getFromTime()){
					continue; // We have to start at Monday so we get duplicates
				}
				$lines = QMStr::getNotEmptyLinesAsArray($cellHtml);
				if(!$lines){
					continue;
				}
				$lines = $this->handleTardy($lines, $subject, $date);
				$lines = $this->handleUnexcused($lines, $subject, $date);
				$lines = $this->handleSick($lines, $subject, $date);
				$assignmentGrades = TigerViewConnector::linesToAssignmentGradeArray($lines, $cellHtml);
				foreach($assignmentGrades as $assignment => $percent){
					$this->addAssignmentGrade($subject, $percent, $assignment, $date);
					$this->addAssignmentDue($subject, $assignment, $date, $percent);
				}
			}
		}
	}
	/**
	 * @param string $subject
	 * @param string $percentage
	 */
	private function addCurrentAverageMeasurement(string $subject, string $percentage){
		if(empty($percentage) && $subject !== "Activity"){
			$this->logInfo("No current average for $subject");
			return;
		}
		$v = $this->getGoalsPercentVariable(self::CURRENT_AVERAGE_GRADE_PREFIX . $subject);
		try {
			$m = $this->generateMeasurement($v, time(), (float)$percentage, PercentUnit::NAME);
		} catch (TooManyMeasurementsException $e) {
			QMLog::info("Skipping this measurement because we have to start import at Monday so we inevitably get duplicate measurements. " .
				" Exception: " . $e->getMessage());
			return;
		}
		$m->setUrl(self::GET_IT_URL);
		try {
			$v->addToMeasurementQueue($m);
		} catch (InvalidVariableValueAttributeException $e) {
			le($e);
		}
		$min = $v->getMinimumAllowedSecondsBetweenMeasurements();
		lei($min !== 86400, "Min seconds is $min");
	}
	/**
	 * @param string $name
	 * @return QMUserVariable
	 */
	private function getGoalsPercentVariable(string $name): QMUserVariable{
		$v = $this->getQMUserVariable($name, PercentUnit::NAME, GoalsVariableCategory::NAME,
			$this->getNewVariableParams(86400));
		return $v;
	}
	/**
	 * @param string $subject
	 * @param float $percent
	 * @param string $assignment
	 * @param string $date
	 */
	private function addAssignmentGrade(string $subject, float $percent, string $assignment, string $date): void{
		$v = $this->getAssignmentGradeVariable($subject);
		try {
			$m = $this->generateMeasurement($v, $date, $percent, PercentUnit::NAME);
		} catch (TooManyMeasurementsException $e) {
			QMLog::info("Skipping this measurement because we have to start import at Monday so we inevitably get duplicate measurements. " .
				" Exception: " . $e->getMessage());
			return;
		}
		$m->setMessage($assignment);
		$m->setUrl(self::GET_IT_URL);
		try {
			$v->addToMeasurementQueue($m);
		} catch (InvalidVariableValueAttributeException $e) {
			le($e);
		}
	}
	/**
	 * @param string $subject
	 * @param string $assignment
	 * @param string $date
	 * @param string $percent
	 */
	private function addAssignmentDue(string $subject, string $assignment, string $date, string $percent): void{
		$v = $this->getAssignmentDueVariable($subject);
		try {
			$completed = !empty($percent);
			$m = $this->generateMeasurement($v, $date, $completed, YesNoUnit::NAME);
		} catch (TooManyMeasurementsException $e) {
			QMLog::info("Skipping this measurement because we have to start import at Monday so we inevitably get duplicate measurements. " .
				" Exception: " . $e->getMessage());
			return;
		}
		$m->setMessage($assignment . " due $date");
		$m->setUrl(self::GET_IT_URL);
		try {
			$v->addToMeasurementQueue($m);
		} catch (InvalidVariableValueAttributeException $e) {
			le($e);
		}
	}
	/**
	 * @param string $key
	 * @return string
	 */
	private static function getDate(string $key): string{
		$date = QMStr::between($key, "day ", " Day");
		$year = TimeHelper::getCurrentYear();
		if(strtotime($year . "/" . $date) > time() + 86400){
			$year = $year - 1;
		}
		return $year . "/" . $date;
	}
	/**
	 * @return int
	 */
	public function getLatestAssignmentMeasurementStartTime(): ?int{
		$assignmentVariables = $this->getAssignmentVariables();
		$latestForAll = [];
		$latestForConnector = $this->getOrCalculateLatestMeasurementAt();
		foreach($assignmentVariables as $v){
			$at = $v->getLatestNonTaggedMeasurementStartAt();
			if(stripos($at, "00:00:00") === false){
				$at = $v->getLatestNonTaggedMeasurementStartAt();
			}
			if(strtotime($at) > strtotime($latestForConnector)){
				QMLog::exceptionIfTesting("$v->name latest $at is greater than latest for connector $latestForConnector");
			}
			$latestForAll[$v->name] = $at;
		}
		if(!$latestForAll){
			return null;
		}
		$max = max($latestForAll);
		return strtotime($max);
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable[]
	 */
	public static function getCurrentAverageGradeVariables(int $userId): array{
		$r = new GetUserVariableRequest([], $userId);
		$r->setSearchPhrase(self::CURRENT_AVERAGE_GRADE_PREFIX);
		$variables = $r->getVariables();
		return $variables;
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable[]
	 */
	public static function getDailyAverageGradeVariables(int $userId): array{
		$r = new GetUserVariableRequest([], $userId);
		$r->setSearchPhrase(self::CLASS_DAILY_AVERAGE_GRADE_SUFFIX);
		$variables = $r->getVariables();
		return $variables;
	}
	/**
	 * @param $lines
	 * @param string $subject
	 * @param string $date
	 * @return array
	 */
	private function handleTardy(array $lines, string $subject, string $date): array{
		foreach($lines as $key => $value){
			if(stripos($value, "tardy") !== false){
				$v = $this->getQMUserVariable("Tardy for Class", CountUnit::NAME, GoalsVariableCategory::NAME,
					$this->getNewVariableParams(3600));
				$this->generateMeasurementAndAddToQueue($v, $date, $subject);
				unset($lines[$key]);
			}
		}
		return array_values($lines);
	}
	/**
	 * @param $lines
	 * @param string $subject
	 * @param string $date
	 * @return array
	 */
	private function handleUnexcused($lines, string $subject, string $date): array{
		foreach($lines as $key => $value){
			if(stripos($value, "UNEXCUSED") !== false){
				$v = $this->getQMUserVariable("Unexcused for Class", CountUnit::NAME, GoalsVariableCategory::NAME,
					$this->getNewVariableParams(3600));
				$this->generateMeasurementAndAddToQueue($v, $date, $subject);
				unset($lines[$key]);
			}
		}
		return array_values($lines);
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getAssignmentVariables(): array{
		if($this->assignmentVariables !== null){
			return $this->assignmentVariables;
		}
		$qb = QMUserVariable::qb(true)->where(UserVariable::TABLE . '.' . UserVariable::FIELD_USER_ID,
			$this->getUserId())->whereLike(Variable::TABLE . '.' . Variable::FIELD_NAME, '%' .
			self::CLASS_DAILY_AVERAGE_GRADE_SUFFIX . '%"');
		/** @var QMUserVariable[] $variables */
		$variables = $qb->getDBModels();
		foreach($variables as $v){
            if(!$v->name){
                le('No $v->name');
            }
			if($v->hasFillingValue()){
				le('$v->hasFillingValue()');
			}
			if($v->fillingType === BaseFillingTypeProperty::FILLING_TYPE_ZERO){
				le('$v->fillingType === BaseFillingTypeProperty::FILLING_TYPE_ZERO');
			}
		}
		return $this->assignmentVariables = $variables;
	}
	/**
	 * @param string $subject
	 * @return QMUserVariable
	 */
	private function getAssignmentGradeVariable(string $subject): QMUserVariable{
		$v = $this->getGoalsPercentVariable($subject . self::CLASS_DAILY_AVERAGE_GRADE_SUFFIX);
		$parents = $v->getParentCommonTagVariables();
		if(!$parents){
			try {
				$v->addParentCommonTag(DailyAverageGradeCommonVariable::NAME);
			} catch (InvalidTagCategoriesException $e) {
				le(__METHOD__.": ".$e->getMessage());
			}
		}
		$parents = $v->getParentCommonTagVariables();
		if(!$parents){
			le("No Parents!");
		}
		foreach($parents as $parent){
			$parent->unsetMeasurements(); // Otherwise we have outdated ones when trying to post later
		}
		return $v;
	}
	/**
	 * @param string $subject
	 * @return QMUserVariable
	 */
	private function getAssignmentDueVariable(string $subject): QMUserVariable{
		$v = $this->getQMUserVariable($subject . self::CLASS_ASSIGNMENT_SUFFIX, YesNoUnit::NAME,
			GoalsVariableCategory::NAME, $this->getNewVariableParams(86400));
		return $v;
	}
	/**
	 * @param $lines
	 * @param string $subject
	 * @param string $date
	 * @return array
	 */
	private function handleSick($lines, string $subject, string $date): array{
		foreach($lines as $key => $value){
			if(stripos($value, "SICK") !== false){
				$v = $this->getQMUserVariable("Sick of School", CountUnit::NAME, GoalsVariableCategory::NAME,
					$this->getNewVariableParams(3600));
				$this->generateMeasurementAndAddToQueue($v, $date, $subject);
				unset($lines[$key]);
			}
		}
		return array_values($lines);
	}
	/**
	 * @param array $lines
	 * @param string $html
	 * @return array
	 */
	public static function linesToAssignmentGradeArray(array $lines, string $html): array{
		$assignmentGrades = [];
		foreach($lines as $i => $line){
			if(strpos($line, " ") !== false){
				QMLog::debug("Assuming line\n$line\nis not a grade because it has a space");
				continue;
			}
			$percent = QMStr::fractionStringToPercent($line, $html);
			if($percent > 200){ // We can get extra credit over 100%
				QMLog::error("Skipping because $percent eval produces $percent%\nFull String:\n$html");
				continue;
			}
			if($percent === null){
				continue;
			}
			if(!isset($lines[$i - 1])){
				QMLog::error("No assignment for grade $line");
				continue;
			}
			$assignment = $lines[$i - 1];
			$assignmentGrades[$assignment] = $percent;
		}
		return $assignmentGrades;
	}
	/**
	 * @param int $minSeconds
	 * @return array
	 */
	private function getNewVariableParams(int $minSeconds): array{
		return [
			Variable::FIELD_IMAGE_URL => BooksVariableCategory::IMAGE_URL,
			Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => $minSeconds,
		];
	}
	/**
	 * @return string
	 */
	public function getAbsoluteFromAt(): string{
		return db_date("12 August 2019");
	}
	/**
	 * @return GradeReport
	 */
	public function getReport(): GradeReport{
		if($this->report){
			return $this->report;
		}
		$r = new GradeReport($this->getUserId());
		return $this->report = $r;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getDailyAverageQMUserVariable(): QMUserVariable{
		$v = $this->getReport()->getDailyAverageQMUserVariable();
		return $v;
	}
	/**
	 * @return QMSendgrid
	 */
	public function email(): QMSendgrid{
		$r = $this->getReport();
		$r->generateAndUploadHtmlAndPost();
		return $r->email();
	}
	public function getLatestAssignmentMeasurementAt(): ?string{
		$time = $this->getLatestAssignmentMeasurementStartTime();
		if(!$time){
			return null;
		}
		return db_date($time);
	}
	/**
	 * @return int
	 */
	public function getFromTime(): int{
		if($this->fromTime){
			return $this->fromTime;
		}
		$latestAssignment = $this->getLatestAssignmentMeasurementStartTime();
		if($latestAssignment){
			$this->setFromDate($latestAssignment);
			return $latestAssignment;
		}
		return parent::getFromTime();
	}
	public static function addConnectorIdToMeasurements(){
		$connectors = static::getQMConnectors();
		foreach($connectors as $c){
			$vars = $c->getAssignmentVariables();
			foreach($vars as $var){
				$uv = $var->l();
				/** @var Measurement[] $measurements */
				$measurements = $uv->measurements()->whereNull(Measurement::FIELD_CONNECTOR_ID)->get();
				foreach($measurements as $m){
					$m->connector_id = $c->getId();
					$m->client_id = $c->getNameAttribute();
					$m->connection_id = $c->getConnectionIfExists()->id;
				}
			}
		}
	}
	/**
	 * @param QMUserVariable|null $v
	 * @param string $date
	 * @param string $subject
	 */
	private function generateMeasurementAndAddToQueue(QMUserVariable $v, string $date, string $subject): void{
		try {
			$m = $this->generateMeasurement($v, $date, 1, CountUnit::NAME);
			$m->setMessage($subject);
			$m->setUrl(self::GET_IT_URL);
			$v->addToMeasurementQueue($m);
		} catch (TooManyMeasurementsException $e) {
			QMLog::info("Skipping this measurement because we have to start import at Monday so we inevitably get duplicate measurements. " .
				" Exception: " . $e->getMessage());
		} catch (InvalidVariableValueAttributeException $e) {
			le($e);
			le($e);
		}
	}
	/**
	 * @param $class
	 * @return string
	 */
	private static function pluckSubjectName($class): string{
		$lines2 = preg_split('/\r\n|\r|\n/', $class);
		$subject = QMStr::getBeforeNumbers($lines2[0]);
		return $subject;
	}
	/**
	 * @throws ConnectorException
	 */
	protected function login(): void{
		$html = $this->fetchHtml(self::LOGIN_URL, [], [], []);
		$token = QMStr::between($html, '"__RequestVerificationToken" type="hidden" value="', '"');
		$pwd = $this->getConnectorPassword();
		if(!$pwd){
			le("No password!");
		}
		$loginParameters = [ // Create an array of POST parameters, and send it to login
			'LogOnDetails.UserName' => $this->getConnectorUserName(true),
			'LogOnDetails.Password' => $pwd,
			'Database' => 10,
			'tempUN' => null,
			'tempPW' => null,
			'SCKTY00328510CustomEnabled' => 'False',
			'SCKTY00436568CustomEnabled' => 'False',
			'VerificationOption' => 'UsernamePassword',
			'__RequestVerificationToken' => $token,
		];
		// Disable redirecting so that we aren't redirected after authentication
		$this->post(self::LOGIN_URL, $loginParameters, ['allow_redirects' => false]);
	}
}
