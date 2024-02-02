<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\DevOps\TestPath;
use App\Exceptions\InvalidResponseDataException;
use App\Exceptions\RateLimitConnectorException;
use App\Files\Bash\BashScriptFile;
use App\Utils\Env;
abstract class AbstractWebComputer extends JenkinsSlave {
	/**
	 * @return string
	 */
	abstract protected static function healthCheckString(): string;
	protected static function healthCheckPath(): string{return "/";}
	/**
	 * @return void
	 */
	abstract public function deploy(): void;
	abstract public function getWebHostname(): string;
	public function isWeb(): bool{return true;}
	public static function instancePrefix(): string{
		return self::LABEL_WEB;
	}
	/**
	 * @return \App\Computers\JenkinsSlave
	 */
	protected function getCypressComputer(): JenkinsSlave{
		$c = JenkinsSlave::whereLabeledAndOnline(self::LABEL_CYPRESS)->first();
		return $c;
	}
	/**
	 * @throws \App\ShellCommands\CommandFailureException
	 */
	public function test(): bool {
		$c = $this->getCypressComputer();
		$process = $c->execInQMAPI("source ".BashScriptFile::SCRIPT_UI_TESTS);
		return $process->getExitCode() === 0;
	}
	public static function tags(): array{
		return [
			['key' => self::LABEL_WEB, 'value' => null],
			['key' => Env::APP_ENV, 'value' => static::getReleaseStage()],
			['key' => LightsailInstanceResponse::TAG_HEALTH_CHECK_STRING, 'value' => static::healthCheckString()],
			['key' => LightsailInstanceResponse::TAG_HEALTH_CHECK_URL, 'value' => self::healthCheckPath()],
		];
	}
	/**
	 * @return TestPath[]
	 */
	abstract public function getHealthCheckPaths(): array;
	public function needToReboot(): ?string {
		$testPaths = $this->getHealthCheckPaths();
		foreach($testPaths as $testPath){
			try {
				$testPath->validate($this->getIP());
			} catch (InvalidResponseDataException | RateLimitConnectorException $e) {
				return $e->getMessage();
			}
		}
		return null;
	}
}
