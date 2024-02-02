<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\Buttons\Admin\ClockworkButton;
use App\Buttons\Admin\LiveProfButton;
use App\Buttons\Admin\TelescopeButton;
use App\Buttons\QMButton;
use App\DevOps\TestPath;
use App\Files\Bash\BashScriptFile;
use App\Folders\AbstractFolder;
use App\Menus\Admin\DebugMenu;
use App\Repos\QMAPIRepo;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\OfflineException;
use Symfony\Component\Process\Process;
abstract class AbstractApiComputer extends AbstractWebComputer {
	protected static function healthCheckString(): string{return "Sign Up";}
	protected static function healthCheckPath(): string{return "/auth/register";}
	public function getHealthCheckPaths(): array{
		return [
			//new TestPath(static::healthCheckPath(), static::healthCheckString()),
			new TestPath("/variables/Anxiety", "Anxiety"),
		];
	}
	/**
	 * @param string $releaseStage
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function generateEnv(string $releaseStage){
		$this->execInQMAPI("export APP_ENV=$releaseStage && php scripts/php/env.php");
	}
	/**
	 * @param string $src
	 * @param string $dest
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function copyFile(string $src, string $dest){
		$this->execInQMAPI("cp $src $dest");
	}
	/**
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function build(){
		$this->gitCloneOrPull(QMAPIRepo::getLongCommitShaHashFromGit());
		$RELEASE_STAGE = static::getReleaseStage();
		$prefix="export RELEASE_STAGE=$RELEASE_STAGE &&";
		$this->generateEnv($RELEASE_STAGE);
		//$this->composer_install();
		$this->execInQMAPI("$prefix source ".BashScriptFile::SCRIPT_BUILD_ON_WEB_SERVER);
		$this->execInQMAPI("$prefix source ".BashScriptFile::SCRIPT_SYNC_TO_RELEASE_FOLDER);
	}
	public function getClockworkButton(): ClockworkButton {
		return new ClockworkButton($this);
	}
	public function logClockworkUrl(): void{
		$this->getClockworkButton()->logUrl();
	}
	public function getTelescopeButton(): TelescopeButton{
		return new TelescopeButton($this);
	}
	public function logTelescopeUrl(): void{
		$this->getTelescopeButton()->logUrl();
	}
	public function getProfilerButton(): QMButton{
		return new LiveProfButton($this);
	}
	public function logProfilerUrl(): void{
		$this->getProfilerButton()->logUrl();
	}
	public function getTDDButton(): QMButton{
		return new TdddButton($this);
	}
	public function logTdddUrl(): void{
		$this->getTDDButton()->logUrl();
	}
	public function logDebugUrls(){
		$this->getDebugMenu()->logUrls();
	}
	public function getDebugMenu(): DebugMenu{
		return new DebugMenu( $this);
	}
	/**
	 * @param string $TEST_PATH
	 * @return \Symfony\Component\Process\Process
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function phpunit_tests(string $TEST_PATH): Process{
		if(empty($TEST_PATH)){le("please provide test path");}
		$resultFolder = AbstractFolder::PATH_BUILD_LOGFILES;
		$jUnit = "$resultFolder/junit.xml";
		$this->cleanFolder($resultFolder);
		$opts="--stop-on-error --stop-on-failure --log-junit $jUnit";
		$cmd = "vendor/phpunit/phpunit/phpunit --configuration phpunit.xml $opts $TEST_PATH";
		$proc = $this->execInQMAPI($cmd);
		return $proc;
	}
	/**
	 * @param string $TEST_PATH
	 * @return \Symfony\Component\Process\Process
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function staging_phpunit_tests(string $TEST_PATH): Process{
		$proc = $this->execute("export LOG_LEVEL=INFO &&
export TEST_PATH=$TEST_PATH &&
export PROFILE=false &&
export ABORT_IF_OTHER_TESTS_FAILED=1 &&
source tests/production_tests.sh");
		return $proc;
	}
	/**
	 * @return void
	 * @throws \App\ShellCommands\CommandFailureException
	 * @throws \App\ShellCommands\OfflineException
	 */
	public function deploy(): void{
		$this->etcFilesCopy();
		$this->composer_install();
	}
	public function getWebHostname(): string{
		return str_replace("https://", "", \App\Utils\Env::getAppUrl());
	}
	/**
	 * @param string $string
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function artisan(string $string){
		$this->execInQMAPI("php artisan ".$string);
	}
}
