<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Buttons\Admin\IgnitionButton;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Buttons\Admin\JenkinsJobButton;
use App\Buttons\Admin\PHPStormExceptionButton;
use App\Buttons\Admin\PHPUnitButton;
use App\Computers\ThisComputer;
use App\Console\Kernel;
use App\DevOps\Jenkins\JenkinsAPI;
use App\DevOps\Jenkins\JenkinsJob;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\GitLockException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMIgnition;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Override\QMErrorPageHandler;
use App\PhpUnitJobs\Code\GitJob;
use App\Solutions\BaseRunnableSolution;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\Markdown;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\QMRoute;
use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\MergeIntoOpenApi;
use Tests\QMBaseTestCase;
use Throwable;
class QMAPIRepo extends GitRepo {
	public const PUBLIC = false;
	public static $REPO_NAME = 'cd-public';
	public const DEFAULT_BRANCH = 'develop';
	public const RELATIVE_PATH = '';
	/**
	 * @return string
	 */
	public static function getAbsPath(): string{
		$repoPath = FileHelper::projectRoot();
		return $repoPath;
	}
	public static function getBranchFromMemory():?string{
		if(!empty(static::$branchByRepo[static::class])){
			return static::$branchByRepo[static::class];
		}
		$branchName = self::getBranchFromEnv();
		if(empty($branchName)){return null;}
		return $branchName;
	}
	/**
	 * @return string
	 */
	public static function getCommitShaHash(){
		if(static::$commitSha !== null){
			return static::$commitSha;
		}
		$commitSha = false;
		if(Env::get('parse_git_hash')){
			$commitSha = Env::get('parse_git_hash');
		}
		if(!$commitSha && Env::get(JenkinsAPI::GIT_COMMIT_SHA_HASH)){
			$commitSha = Env::get(JenkinsAPI::GIT_COMMIT_SHA_HASH);
		}
		if(!$commitSha && Env::get('CIRCLE_SHA1')){
			$commitSha = Env::get('CIRCLE_SHA1');
		}
		if(!$commitSha){
			//$commitSha = self::getShortCommitShaHashFromGit();
			$commitSha = self::getLongCommitShaHash();
		}
		return static::$commitSha = $commitSha;
	}
	/**
	 * @return string
	 */
	public static function getCommitDateFromEnvOrGit(): string{
		if(Env::get('API_LAST_MODIFIED')){
			return Env::get('API_LAST_MODIFIED');
		}
		return self::getCommitDate();
	}
	/**
	 * @return string
	 */
	public static function getCommitHashAndDate(): string{
		return self::getCommitShaHash() . '(' . self::getCommitDateFromEnvOrGit() . ')';
	}
	public static function build(){
		ThisComputer::exec("npm install");
		ThisComputer::exec("composer install");
	}
	/**
	 * @throws GitLockException
	 */
	public static function generateAndCommitLaravelDocs(){
		//FileHelper::deleteDir("resources/docs");
		self::generateOpenApiSpec();
		static::createFeatureBranch("api-docs");
		Kernel::artisan("apidoc:generate", []);
		static::addFilesInFolder('public/docs');
		static::addFilesInFolder('storage/responses');
		static::commitAndPush("Updated API docs");
	}
	public static function generateOpenApiSpec(): void{
		$openapi = new \OpenApi\Annotations\OpenApi([
			'info' => new Info([
				'title' => config('app.name') . ' API',
				'version' => "v6",
			]),
		]);
		$annotations = [$openapi];
		$routes = QMRoute::getAPIRoutes();
		foreach($routes as $route){
			$m = $route->getModel();
			$annotations[$route->uri] = $route->getSwaggerPath();
			$annotations[$m->getShortClassName()] = $m->getOpenApiSchema();
		}
		$analysis = new Analysis($annotations);
		$analysis->process(new MergeIntoOpenApi());
		$analysis->process(new BuildPaths());
		/** @noinspection PhpUnhandledExceptionInspection */
		$openapi->validate();
		if(QMRoute::usingOpenApiV3()){
			/** @noinspection PhpUnhandledExceptionInspection */
			$openapi->saveAs(public_path("open-api-v3.json"));
		} else{
			/** @noinspection PhpUnhandledExceptionInspection */
			$openapi->saveAs(public_path("swagger-v2.json"));
		}
	}
	public static function createFeatureBranchForEachModifiedFile(): void{
		$paths = QMAPIRepo::getChangedFiles();
		foreach($paths as $path){
			if(FileHelper::pathToClass($path) === GitRepo::class){
				continue;
			}
			if(FileHelper::pathToClass($path) === GitJob::class){
				continue;
			}
			$file = QMStr::afterLast($path, DIRECTORY_SEPARATOR);
			$featureName = str_replace('.php', '', $file);
			$featureName = QMStr::snakize($featureName);
			$featureName = QMStr::slugify($featureName);
			QMAPIRepo::createFeatureBranch($featureName);
			$message = str_replace('.php', '', $file);
			QMAPIRepo::addAndCommit($message, $path);
			$branch = "feature/$featureName";
			QMAPIRepo::push($branch, QMAPIRepo::getRemoteUrlWithToken());
		}
	}
	public static function cleanResetAndUpdateSubModules(){
		le("Don't use submodules for qm-api. Trust me, it's not worth it. ");
	}
	/**
	 * @param Throwable $e
	 * @return string
	 */
	public static function renderMarkdown(Throwable $e): string{
		$md = "### Problem\n".ExceptionHandler::getAbbreviatedMessage($e)."\n";
		if(AppMode::getCurrentTestName()){
			$md .= self::testMDBox();
		}
		if($job = JenkinsJob::getCurrentJobName()){
			$md .= (new JenkinsConsoleButton())->getMarkdownBadge();
		}
		$md .= (new PHPStormExceptionButton($e))->getMarkdownBadge()."\n";
		$md .= (new IgnitionButton($e))->getMarkdownBadge()."\n";
		$md = Markdown::addIcons($md);
		if($tests = SolutionButton::addUrlNameArrays([], $e)){
			$md .= Markdown::hiddenListMD("Solution Links", $tests);
		}
		$solutions = QMErrorPageHandler::getSolutions($e);
		$md .= "\n## Solutions\n";
		foreach($solutions as $solution){
			$md .= BaseRunnableSolution::solutionToMarkdown($solution);
		}
		// Just use ignition page because it's too slow to load large comments $md .= Markdown::hiddenMD("Surrounding Code", $stack->getStackLinksWithArgumentsMD());
		$md = QMStr::truncate($md, 65536, "[TRUNCATED BECAUSE github-comment must be shorter than 65536]");
		$md = mb_convert_encoding($md, 'UTF-8', 'UTF-8');
		return $md;
	}
	/**
	 * @return string
	 */
	public static function testMDBox(): string{
		$duration = QMBaseTestCase::getTestDuration();
		$job = (AppMode::isJenkins()) ? (new JenkinsJobButton())->getMarkdownLink(false) : "No Jenkins";
		$branch = static::getBranchButton()->getMarkdownLink(false);
		$testLink = PHPUnitButton::getForCurrentTest()->getMarkdownLink(false);
		return "
---
| Test      | Duration           | Job      | Branch  |
| :-------: | :----------------: | :------: | :-----: |
|  $testLink    | $duration seconds  |   $job   | $branch |
---
";
	}
	/**
	 * @param Throwable $e
	 * @return array
	 */
	public static function githubComment(Throwable $e): array{
		$md = self::renderMarkdown($e);
		try {
			return static::createComment($md, static::getCommitShaHash());
		} catch (\Throwable $e) {
		    ConsoleLog::info("Failed to create github comment because: ".$e->getMessage());
			return [];
		}
	}
	public static function getRepoName(): string{
		if(static::$REPO_NAME){
			return static::$REPO_NAME;
		}
		return static::$REPO_NAME = basename(shell_exec("git rev-parse --show-toplevel"));
	}
	public static function createFailedStatus(Throwable $e, $shortName = null){
		if(!$shortName){
			$shortName = AppMode::getJobTaskOrTestName();
			if(!$shortName){
				le("No JobTaskOrTestName for Github status context!");
			}
		}
		$description = $e->getMessage();
		if(isset(QMBaseTestCase::$testStartTime)){
			$description = QMBaseTestCase::getTestDuration()."s ".$e->getMessage();
		}
		$description = QMBaseTestCase::getTestDuration()."s ".$e->getMessage();
		$state = static::STATE_failure;
		try {
			$url = QMIgnition::getUrlOrGenerateAndOpen($e);
			static::createStatus(QMAPIRepo::getCommitShaHash(), $state, $url, $shortName,
			                     $description);
		} catch (Throwable $e) {
			QMLog::error($e->getMessage()." Could not setFailedGithubStatus!", [
				"message_tried_to_set" => "Tried to say:
state: $state
shortName: $shortName
description: $description",
			]);
		}
	}
	public static function setFinalStatus(){
		$failures = QMBaseTestCase::getFailedTests();
		$suiteDuration = QMBaseTestCase::getSuiteDuration();
		if($failures){
			$failures = implode(", ", array_keys($failures));
			return QMAPIRepo::createStatus(QMAPIRepo::getLongCommitShaHash(), "failure", ThisComputer::getBuildUrl(), 
			                            QMBaseTestCase::getSuiteName(),
			                          "Failures on ".ThisComputer::getComputerName()." after $suiteDuration:
$failures");
		}
		return QMAPIRepo::createStatus(QMAPIRepo::getLongCommitShaHash(), "success", ThisComputer::getBuildUrl(), 
		                            QMBaseTestCase::getSuiteName(),
		                         "Took ".$suiteDuration." on ".ThisComputer::getComputerName());
	}
	/**
	 * @param array $statuses
	 * @param mixed $suite
	 * @return string
	 */
	public static function getStatusTable(array $statuses, mixed $suite): string{
		return QMLog::table($statuses, "Statuses for $suite");
	}
	public static function setSuitePending(string $suite = null){
		static::setStatusPending($suite ?? QMBaseTestCase::getSuiteName(),
		                          "Started ".TimeHelper::hourMinute(time(), "America/Chicago")." on ".
		                          ThisComputer::getComputerName());
	}
	public static function suiteStatusIsError(string $suite  = null){
		$status = static::getStatus($suite);
		if(!$status){return false;}
		return $status->state === static::STATE_error;
	}
	public static function suiteStatusIsPending(string $suite  = null){
		$status = static::getStatus($suite);
		if(!$status){return false;}
		return $status->state === static::STATE_pending;
	}
	public static function suiteStatusIsSuccess(string $suite  = null){
		$status = static::getStatus($suite);
		if(!$status){return false;}
		return $status->state === static::STATE_success;
	}
	public static function suiteStatusIsFailure(string $suite  = null){
		$status = static::getStatus($suite);
		if(!$status){return false;}
		return $status->state === static::STATE_failure;
	}
	public static function getStatus(string $suite  = null){
		if(!$suite){$suite = QMBaseTestCase::getSuiteName();}
		$sha = static::getLongCommitShaHash();
		$statuses = static::statusesForContext($suite);
		if($statuses->count() === 0){
			return null;
		}
		$status = $statuses->first();
		return (object)$status;
	}
	public static function alreadyTesting($suite = null){
		if(!$suite){$suite = QMBaseTestCase::getSuiteName();}
		if($statuses = static::statusesForContext($suite)){
			$statuses = $statuses->filter(function($status){
				$status = (object)$status;
				if(!TimeHelper::inLastXMinutes($status->updated_at, 30)){
					return null;
				}
				return $status;
			}); 
			if($statuses->count() > 0){
				$statuses = $statuses->map(function($status){
					$status = (object)$status;
					return [
						"context" => $status->context,
						"state" => $status->state,
						"description" => $status->description,
						"last updated" => TimeHelper::minutesAgo($status->updated_at),
					];
				})->toArray();
				return static::getStatusTable($statuses, $suite);
			}
		}
		return false;
	}
	public static function logErrorIfAlreadyTesting($suite = null){
		if(!$suite){
			$suite = QMBaseTestCase::getSuiteName();
		}
		if($statusTable = self::alreadyTesting()){
			QMLog::error("$suite is already running somewhere else\n".
			                             $statusTable);
			//die(1);
		}
	}
	/**
	 * @return mixed
	 */
	public static function getBranchFromEnv(): mixed{
		$branchName = Env::get('BRANCH');
		if(!$branchName && Env::get('BRANCH_NAME')){
			$branchName = Env::get('BRANCH_NAME');
		}
		if(!$branchName && Env::get('GIT_BRANCH')){
			$branchName = Env::get('GIT_BRANCH');
		}
		return $branchName;
	}
}
