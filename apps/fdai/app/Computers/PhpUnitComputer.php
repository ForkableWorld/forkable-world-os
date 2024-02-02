<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\DevOps\Jenkins\Jenkins;
use App\Repos\QMAPIRepo;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\OfflineException;
use App\Utils\Env;
use App\Utils\ReleaseStage;
class PhpUnitComputer extends AbstractApiComputer {
	use OnLightsail;
	public const ALL_LABELS = 'phpunit staging-phpunit homestead nodejs tideways docker ubuntu cypress phpunit-jobs';
	const BLUEPRINT_SNAPSHOT_NAME = 'aapanel-master-4';
	const FOLDER_WORKSPACE = "/var/jenkins/workspace";
	const PREFIX_PHPUNIT = "phpunit";
	public static function createJenkinsNodes(){
		Jenkins::createJenkinsNodes(static::instancePrefix());
	}
	public static function ownWorkSpaces(string $name = null){
		$all = static::all();
		foreach($all as $one){
			if($name && $one->getNameAttribute() !== $name){continue;}
			try {
				$one->ownFolder(self::FOLDER_WORKSPACE);
			} catch (CommandFailureException $e) {
				le($e);
			}
		}
	}
	public static function getReleaseStage(): string{
		return ReleaseStage::TESTING;
	}
    public static function recreateAll(){
		$all = static::all();
		foreach ($all as $one) {
			$one->recreate();
		}
    }
	public function recreate(){
		$this->deleteJenkinsNodeAndLightSailInstance(__METHOD__);
		static::createInstance();
	}
	public static function deleteAll(){
		$all = static::all();
		foreach ($all as $one) {
			$one->deleteJenkinsNodeAndLightSailInstance(__METHOD__);
		}
	}
	public function isWeb(): bool{ return false; }
	public function needToReboot(): ?string{ return $this->offline ? "Jenkins said I'm offline" : null; }
	public static function instancePrefix(): string{
		return self::PREFIX_PHPUNIT;
	}
	public static function tags(): array{
		return [
			['key' => 'nodejs', 'value' => null],
			['key' => 'phpunit', 'value' => null],
			['key' => 'phpunit-jobs', 'value' => null],
			['key' => 'staging-phpunit', 'value' => null],
			['key' => 'tideways', 'value' => null],
			['key' => Env::APP_ENV, 'value' => static::getReleaseStage()],
		];
	}
	public static function getBlueprintSnapshot(): LightsailSnapshot {
		return LightsailSnapshot::find(self::BLUEPRINT_SNAPSHOT_NAME);
	}
	/**
	 * @throws CommandFailureException|OfflineException
	 */
	public function test(): bool {
		$this->execute("sudo usermod -a -G www ".$this->getNonRootUser());
		$this->gitCloneOrPull(QMAPIRepo::getLongCommitShaHash());
		$this->composer_install();
		$this->execInQMAPI("sudo chown -R ".$this->getNonRootUser().":www $(ls -I .user.ini)");
		$path = Env::getFormatted('TEST_PATH') ?? "tests/UnitTests";
		$proc = $this->phpunit_tests($path);
		return $proc->getExitCode() === 0;
	}
	/**
	 * @return void
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function deploy(): void{
		$this->etcFilesCopy();
		$this->composer_install();
	}
	public function getWebHostname(): string{
		return $this->getIP();
	}
	public function getPublicPorts(): array{return [];}
	/**
	 * @throws OfflineException
	 * @throws CommandFailureException
	 */
	public function aaPanelConfig(){
		$this->gitCloneOrPull();
		$this->executeScript("scripts/aapanel_nginx_config.sh", true);
	}
}
