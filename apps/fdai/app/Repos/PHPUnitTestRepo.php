<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Storage\LocalFileCache;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Tests\QMBaseTestCase;
class PHPUnitTestRepo extends GitRepo {
	public static $REPO_NAME = 'qm-api';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'develop';
	const LAST_COMPOSER_INSTALL = "last-composer-install-in-tests";
	const TEST_FAILED = "TEST_FAILED";
	public static function test(string $sha, string $TEST_PATH){
		static::cloneIfNecessary();
		$TEST_PATH = QMStr::afterLast($TEST_PATH, "/");
		static::checkoutCommit($sha);
		if(static::needComposerInstall()){
			static::execute("export APP_ENV=testing && composer install");
			LocalFileCache::set(self::LAST_COMPOSER_INSTALL, time());
		}
		LocalFileCache::set(self::TEST_FAILED, false);
		static::execute("env -i php " .
			//"-dxdebug.remote_enable=1 -dxdebug.remote_mode=req -dxdebug.remote_port=9000 -dxdebug.client_host=127.0.0.1 -dxdebug.remote_connect_back=0 ".
			"./vendor/phpunit/phpunit/phpunit " . "--configuration phpunit.xml " . "--stop-on-error " .
			"--stop-on-failure " . QMBaseTestCase::PATH_JUNIT." $TEST_PATH", false);
		$failed = LocalFileCache::get(self::TEST_FAILED);
		if($failed){
			le("$failed failed!");
		}
	}
	protected static function needComposerInstall(): bool{
		return static::composerJsonLastModified() > static::lastComposerInstall();
	}
	/**
	 * @return int
	 */
	public static function composerJsonLastModified(): int{
		$last = FileHelper::getLastModifiedTime('composer.lock');
		\App\Logging\ConsoleLog::info("composer.lock last modified " .
			TimeHelper::timeSinceHumanString($last));
		return $last;
	}
	/**
	 * @return int
	 */
	public static function lastComposerInstall(): int{
		$time = LocalFileCache::get(self::LAST_COMPOSER_INSTALL);
		\App\Logging\ConsoleLog::info("Last composer install " . TimeHelper::timeSinceHumanString($time));
		return $time;
	}
}
