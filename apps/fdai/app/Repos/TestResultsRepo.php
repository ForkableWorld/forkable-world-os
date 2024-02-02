<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Jobs\PHPUnitTestJob;
use App\Menus\Admin\TestFailureMenu;
use App\Utils\AppMode;
use Tests\QMBaseTestCase;
use Throwable;
class TestResultsRepo extends GitRepo {
	const LAST_FAILURE_URLS_TXT = "last-failure-urls.txt";
	public static $REPO_NAME = 'test-results';
	public static function clonePullAndOrUpdateRepo(): void{
		self::deleteIfGitFolderNotPresent();
		parent::clonePullAndOrUpdateRepo();
	}
	/**
	 * @param string|null $filepath
	 * @return string
	 */
	private static function getBranchTestPath(string $filepath = null): string{
		$fullPath = QMAPIRepo::getBranchFromMemoryOrGit() . "/" . AppMode::getCurrentTestName();
		if($filepath){
			$fullPath .= "/" . $filepath;
		}
		return self::getAbsolutePath($fullPath);
	}
	/**
	 * @param QMBaseTestCase|null $test
	 * @param \Throwable $e
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function writeTestFailure($test, Throwable $e): void{
		$test = $test ?? AppMode::getCurrentTest();
		$menu = new TestFailureMenu($e, $test);
		$html = $menu->getHtml();
		self::writeHtml(self::getIndexHtmlPath(), $html);
		self::writeToFile(self::LAST_FAILURE_URLS_TXT, implode("\n", $menu->getUrls()));
	}
	public static function deleteCurrentTestFiles(){
		self::deleteFileOrFolder(self::getBranchTestPath());
		self::deleteFileOrFolder(self::getLastUrlsPath());
	}
	/**
	 * @return string
	 */
	public static function getIndexHtmlPath(): string{
		return self::getBranchTestPath("index.html");
	}
	public static function getLastUrlsPath(): string{
		return self::getAbsolutePath(self::LAST_FAILURE_URLS_TXT);
	}
	public static function queueTestLocally(): void{
		$j = new PHPUnitTestJob(AppMode::getCurrentTestClass(), AppMode::getCurrentTestName(),
			QMAPIRepo::getCommitShaHash());
		$j->dispatch()->onQueue('local');
	}
}
