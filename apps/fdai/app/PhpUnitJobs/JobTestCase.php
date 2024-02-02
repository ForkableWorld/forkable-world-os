<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs;
use App\Buttons\Admin\ClockworkButton;
use App\Buttons\Admin\PHPStormButton;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Models\UserVariable;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Slack\SlackMessage;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Storage\DB\ProductionDB;
use App\Storage\DB\StagingDB;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\HasFunctionLinks;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Tests\TestCase;
class JobTestCase extends TestCase {
    use HasFunctionLinks, UsesClockwork;
    //protected const SLACK_CHANNEL = 'jenkins';
    protected const SLACK_CHANNEL = '#emergency';
    public static $isRunning;
    public static $jobClientId;
    /**
     * @var JobTestCase
     */
    public static $currentJob;
    protected $blackfire;
    protected $debugVariableId;
    protected $debugVariableName;
    protected $debugUserVariable;
    protected $debugCauseUserVariable;
    protected $debugEffectUserVariable;
    protected $debugEffectVariableId;
    protected $debugCauseVariableId;
    public static $startTime;
    public $debugUser;
    /**
     * JobTestCase constructor.
     */
    public function __construct(){
        parent::__construct();
        QMSlim::bootstrapLaravelConsoleAppIfNecessary();
        ini_set("date.timezone", "UTC");
        self::resetStartTime();
        ThisComputer::setWorkerMemoryLimit();
    }
	public function setUp(): void {
		QMLog::logStartOfProcess($this->getName());
		$this->setJobName();
		static::$isRunning = true;
		parent::setUp();
		$this->setUpClockwork();
		if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__));}
	}
	public function tearDown(): void {
		parent::tearDown();
		(new ClockworkButton)->logUrl();
		QMLog::logEndOfProcess($this->getName());
	}
	protected function getAllowedDBNames(): array{
		if(EnvOverride::isLocal()){
			$arr = [StagingDB::getDefaultDBName(), ProductionDB::getDefaultDBName(), TestDB::getDefaultDBName()];
		} else {
			$arr = [ProductionDB::getDefaultDBName()];
		}
		$n = Writable::getDbName();
		if(Writable::getDbName() !== ProductionDB::getDbName()){
			QMLog::warning("Are you sure you want to be running a job on $n? If not, update your root .env file.");
		}
		return $arr;
	}
    /**
     * @param float $maximumJobDuration
     */
    public static function setMaximumJobDuration(float $maximumJobDuration): void{
        \App\Logging\ConsoleLog::info("Set maximum job duration to ".
            TimeHelper::convertSecondsToHumanString($maximumJobDuration));
        Memory::set('MAX_JOB_DURATION', $maximumJobDuration); // Use memory instead of static variables so it gets reset when a test ends
    }
    public static function resetStartTime(): void{
        self::$startTime = time();
    }
    public static function setStartTimeIfNecessary(): void{
        if(!self::$startTime){
            self::resetStartTime();
        }
    }
    /**
     * @return JobTestCase
     */
    public static function getCurrentJob(): ?JobTestCase{
        return self::$currentJob;
    }
    /**
     * @return float
     */
    public static function getJobDurationInSeconds(): float {
        if(!self::$startTime){
           QMLog::error("Job start time was not set! Setting now");
		   self:$startTime = time();
        }
        return time() - self::$startTime;
    }
    /**
     * @return bool
     */
    public static function jobDurationExceedsTimeLimit(): bool {
        $max = self::getMaxJobDuration();
        $duration = self::getJobDurationInSeconds();
        $result = $duration > $max;
        if($result){
            \App\Logging\ConsoleLog::info("Job duration $duration s exceeded $max seconds");
        }
        return $result;
    }
    public static function getMaxJobDuration(): float {
        $max = Memory::get('MAX_JOB_DURATION');
        if($max){return $max;}
        return 30 * 60; // 30 minute default
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function getDebugUserVariable(): QMUserVariable{
        return $this->debugUserVariable ?: $this->setDebugUserVariable();
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function setDebugUserVariable(): QMUserVariable{
        if($this->getDebugVariableId() && $this->getDebugUserId()){
            $this->debugUserVariable = QMUserVariable::getByNameOrId($this->getDebugUserId(), $this->getDebugVariableId());
            QMLog::info("Using debug variable ".$this->debugUserVariable->name." for user ".$this->debugUserVariable->getQMUser()
                    ->getLoginNameAndIdString());
        }
        return $this->debugUserVariable;
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function getDebugCauseUserVariable(): QMUserVariable{
        return $this->debugCauseUserVariable ?: $this->setDebugCauseUserVariable();
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function setDebugCauseUserVariable(): QMUserVariable{
        if($this->getDebugCauseVariableId() && $this->getDebugUserId()){
            $this->debugCauseUserVariable = QMUserVariable::getByNameOrId($this->getDebugUserId(), $this->getDebugCauseVariableId());
            QMLog::info("Using debug CAUSE variable ".$this->debugCauseUserVariable->name." for user ".$this->debugCauseUserVariable->getQMUser()
                    ->getLoginNameAndIdString());
        }
        return $this->debugCauseUserVariable;
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function getDebugEffectUserVariable(): QMUserVariable{
        return $this->debugEffectUserVariable ?: $this->setDebugEffectUserVariable();
    }
    /**
     * @return QMUserVariable
     * @throws UserVariableNotFoundException
     */
    public function setDebugEffectUserVariable(): QMUserVariable{
        if($this->getDebugEffectVariableId() && $this->getDebugUserId()){
            $this->debugEffectUserVariable = QMUserVariable::getByNameOrId($this->getDebugUserId(), $this->getDebugEffectVariableId());
            QMLog::info("Using debug EFFECT variable ".$this->debugEffectUserVariable->name." for user ".$this->debugEffectUserVariable->getQMUser()
                    ->getLoginNameAndIdString());
        }
        return $this->debugEffectUserVariable;
    }
    protected function checkUserVariableRelationshipStats(){
        $numberUpdatedInLastDay = QMUserVariableRelationship::logNumberAnalyzedInLastDay();
        $this->assertGreaterThan(0, $numberUpdatedInLastDay, "No USER user_variable_relationships in last 24 hours!");
        $minutesAgo = QMUserVariableRelationship::getMostRecent()->getMinutesSinceUpdatedAt();
        $this->assertLessThan(86400 / 60, $minutesAgo, "Last USER correlation update was more than a day ago!");
    }
    protected function checkAggregatedCorrelationStats(){
        $numberUpdatedInLastDay = QMGlobalVariableRelationship::logNumberAnalyzedInLastDay();
        $this->assertGreaterThan(0, $numberUpdatedInLastDay, "No global Variable Relationships in last 24 hours!");
        $mostRecent = QMGlobalVariableRelationship::getMostRecentlyAnalyzed();
        $this->assertLessThan(86400 / 60 + 10, $mostRecent->getMinutesSinceUpdatedAt(),
            "Last global Variable Relationship update was more than a day ago!");
    }
    protected function checkUserVariableStats(){
        $numberUpdatedInLastDay = QMUserVariable::logNumberAnalyzedInLastDay();
        $this->assertGreaterThan(0, $numberUpdatedInLastDay, "No USER variables updated in last 24 hours!");
        $lastUpdate = QMUserVariable::readonly()->max(UserVariable::FIELD_ANALYSIS_ENDED_AT);
        $secondsAgo = time() - strtotime($lastUpdate);
        $this->assertLessThan(86400, $secondsAgo, "Last USER variable update was ".TimeHelper::timeSinceHumanString($lastUpdate));
    }
    protected function checkCommonVariableStats(){
        $numberUpdatedInLastDay = QMCommonVariable::logNumberAnalyzedInLastDay();
        $this->assertGreaterThan(0, $numberUpdatedInLastDay, "No COMMON variables updated in last 24 hours!");
        $mostRecent = QMCommonVariable::getMostRecentlyAnalyzed();
        $minutesAgo = $mostRecent->getMinutesSinceLastAnalysis();
        $this->assertNotNull($minutesAgo, "Last COMMON variable never updated!");
        $this->assertLessThan(86400 / 60, $minutesAgo, "Last COMMON variable update was more than a day ago!");
    }
    protected function checkUpdateStats(){
        $this->checkAggregatedCorrelationStats();
        $this->checkCommonVariableStats();
        $this->checkUserVariableRelationshipStats();
        $this->checkUserVariableStats();
    }
    /**
     * @return int
     */
    protected function getDebugUserEmail(): int{
        return \App\Utils\Env::get('DEBUG_USER_EMAIL') ?: \App\Utils\Env::get('DEBUG_EMAIL');
    }
    /**
     * @return int
     */
    protected function getDebugUserId(): ?int{
        if(\App\Utils\Env::get('DEBUG_USER_ID')){
            return intval(\App\Utils\Env::get('DEBUG_USER_ID'));
        }
        if(\App\Utils\Env::get('DEBUG_USER_EMAIL')){
            return QMUser::findByEmail(\App\Utils\Env::get('DEBUG_USER_EMAIL'))->id;
        }
        return null;
    }
    /**
     * @return int
     */
    protected function getDebugVariableId(): ?int{
        if($this->debugVariableId){
            return $this->debugVariableId;
        }
        if(\App\Utils\Env::get('DEBUG_VARIABLE_ID')){
            return $this->debugVariableId = intval(\App\Utils\Env::get('DEBUG_VARIABLE_ID'));
        }
        if(\App\Utils\Env::get('DEBUG_VARIABLE_NAME')){
            return $this->debugVariableId = QMCommonVariable::find(\App\Utils\Env::get('DEBUG_VARIABLE_NAME'))->id;
        }
        return null;
    }
    /**
     * @return int
     */
    protected function getDebugCauseVariableId(): ?int{
        if($this->debugCauseVariableId){
            return $this->debugCauseVariableId;
        }
        if(\App\Utils\Env::get('DEBUG_CAUSE_VARIABLE_ID')){
            return $this->debugCauseVariableId = intval(\App\Utils\Env::get('DEBUG_CAUSE_VARIABLE_ID'));
        }
        if(\App\Utils\Env::get('DEBUG_CAUSE_VARIABLE_NAME')){
            return $this->debugCauseVariableId = QMCommonVariable::find(\App\Utils\Env::get('DEBUG_CAUSE_VARIABLE_NAME'))->id;
        }
        return null;
    }
    /**
     * @return int
     */
    protected function getDebugEffectVariableId(): ?int{
        if($this->debugEffectVariableId){
            return $this->debugEffectVariableId;
        }
        if(\App\Utils\Env::get('DEBUG_EFFECT_VARIABLE_ID')){
            return $this->debugEffectVariableId = intval(\App\Utils\Env::get('DEBUG_EFFECT_VARIABLE_ID'));
        }
        if(\App\Utils\Env::get('DEBUG_EFFECT_VARIABLE_NAME')){
            return $this->debugEffectVariableId = QMCommonVariable::find(\App\Utils\Env::get('DEBUG_EFFECT_VARIABLE_NAME'))->id;
        }
        return null;
    }
    /**
     * @return string
     */
    protected function getDebugVariableName(): ?string{
        if($this->debugVariableName){
            return $this->debugVariableName;
        }
        if(\App\Utils\Env::get('DEBUG_VARIABLE_NAME')){
            return $this->debugVariableName = \App\Utils\Env::get('DEBUG_VARIABLE_NAME');
        }
        if(\App\Utils\Env::get('DEBUG_VARIABLE_ID')){
            return $this->debugVariableName = QMCommonVariable::find(\App\Utils\Env::get('DEBUG_VARIABLE_ID'))->name;
        }
        return null;
    }
    /**
     * @param string $description
     */
    public static function slack(string $description){
        if(AppMode::isTestingOrStaging()){
            return;
        }
        $m = new SlackMessage(JobTestCase::getJobName());
        $m->send($description);
    }
    /**
     * @return string
     */
    public static function getJobTaskOrTestName(): ?string{
        return AppMode::getJobTaskOrTestName();
    }
    /**
     * @return string
     */
    public static function getJobName(): ?string{
        return AppMode::getJobOrTaskNameIfNotTesting();
    }
    /**
     * @return OAClient
     */
    public static function getJobClientId(): ?string {
        //if(static::$jobClientId){return static::$jobClientId;}
        $name = JobTestCase::getJobName();
        if(!$name){return null;}
        $client = OAClient::getOrCreate(QMStr::slugify('job-'.$name),
            [OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM]);
        return static::$jobClientId = $client->client_id;
    }
    private function setJobName(): void{
        self::$currentJob = $this;
        $name = str_replace('test', '', $this->getName());
        AppMode::setJobOrTaskName($name);
    }
    public static function getJobPHPStormUrl():string{
        return PHPStormButton::directLink(__FILE__);
    }
	public function getPhpStormButton(): PHPStormButton{
		return new PHPStormButton($this->getName(), $this->getPHPStormUrl());
	}
	public function getPHPStormUrl(): string{
		return PHPStormButton::urlToFunction(static::class, $this->getName());
	}
	/**
	 * @return \Clockwork\Support\Laravel\ClockworkSupport
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function getClockworkSupport(): \Clockwork\Support\Laravel\ClockworkSupport {
		$application = $this->app;
		$make = $application->make('clockwork.support');
		return $make;
	}
}
