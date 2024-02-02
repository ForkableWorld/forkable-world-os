<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Buttons\QMButton;
use App\DataSources\Connectors\Responses\Github\Repo;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Menus\DynamicMenu;
use App\Menus\QMMenu;
use App\Repos\GitRepo;
use App\Types\QMStr;
use App\Utils\AppMode;
abstract class PackageList {
	protected static $repos;
	public $license;
	public $name;
	public $path;
	public $scripts;
	public $version;
	public $languageImage;
	public $resumeHeading;
	public function __construct(string $path){
		$this->path = $path;
		try {
			$obj = FileHelper::readJsonFile($path);
			if(!$obj){
				$obj = FileHelper::readJsonFile($path);
			}
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		foreach($obj as $key => $value){
			$camel = QMStr::camelize($key);
			$this->$camel = $value;
		}
	}
	/**
	 * @param array $repos
	 * @param string $image
	 * @param string $title
	 * @return QMMenu
	 */
	public static function reposToMenu(array $repos, string $image, string $title): QMMenu{
		$repos = self::sortByStars($repos);
		$buttons = QMButton::toButtons($repos);
		foreach($buttons as $button){
			$button->setSecondaryImage($image);
		}
		$menu = DynamicMenu::toMenu($buttons);
		$menu->title = $title;
		$menu->image = $image;
		return $menu;
	}
	/**
	 * @param Repo[] $repos
	 * @return array
	 */
	public static function sortByStars(array $repos): array{
		return collect($repos)->sortByDesc(function($repo){
			/** @var Repo $repo */
			return $repo->stargazers_count;
		})->all();
	}
	/**
	 * @return Repo[]
	 */
	public function getRepositories(): array{
		$repos = [];
		$i = 0;
		$packages = $this->getDependencies();
		foreach($packages as $package){
			if($repo = $this->getRepo($package)){
				$i++;
				$repos[] = $repo;
				if(AppMode::isUnitOrStagingUnitTest() && $i > 2){
					return $repos;
				}
			}
		}
		return $repos;
	}
	abstract protected function getDependencies(): array;
	public function getRepo(string $package): ?Repo{
		if($repo = static::$repos[$package] ?? null){
			return $repo;
		}
		try {
			$url = $this->getGithubUrl($package);
			$owner = PackageList::getGithubOwner($url);
			$repo = PackageList::getGithubRepoName($url);
			return static::$repos[$package] = GitRepo::getRepoResponseObject($owner, $repo);
		} catch (NotFoundException $e) {
			QMLog::info($package . ": " . $e->getMessage());
			return null;
		}
	}
	public function generateHtml(): string{
		$repos = $this->getRepositories();
		return self::reposToList($repos, $this->resumeHeading, $this->languageImage);
	}
	/**
	 * @param Repo[] $repos
	 * @param string $title
	 * @param string $secondaryImage
	 * @return string
	 */
	public static function reposToList(array $repos, string $title, string $secondaryImage): string{
		$menu = self::reposToMenu($repos, $secondaryImage, $title);
		$html = $menu->getMDLAvatarList();
		return $html;
	}
	/**
	 * @param Repo[] $repos
	 * @param string $title
	 * @param string $secondaryImage
	 * @return string
	 */
	public static function reposToChips(array $repos, string $title, string $secondaryImage): string{
		$menu = self::reposToMenu($repos, $secondaryImage, $title);
		$html = $menu->getSmallTailwindChipsHtml();
		return $html;
	}
	/**
	 * @param Repo[] $repos
	 * @param string $title
	 * @param string $secondaryImage
	 * @return string
	 */
	public static function reposToYAML(array $repos, string $title, string $secondaryImage): string{
		$menu = self::reposToMenu($repos, $secondaryImage, $title);
		$html = $menu->getYAML();
		return $html;
	}
	/**
	 * @param Repo[] $repos
	 * @param string $title
	 * @param string $secondaryImage
	 * @return array
	 */
	public static function reposToArray(array $repos, string $title, string $secondaryImage): array{
		$menu = self::reposToMenu($repos, $secondaryImage, $title);
		return $menu->toArray();
	}
	abstract public function getGithubUrl(string $ownerRepo): string;
	public static function getGithubOwner(string $url): string{
		$owner = $url;
		$owner = QMStr::between($owner, "https://gitlab.com/", "/", $owner);
		$owner = QMStr::between($owner, "https://", ".github.io", $owner);
		$owner = QMStr::between($owner, "api.github.com/repos/", "/", $owner);
		$owner = QMStr::between($owner, "github.com/", "/", $owner);
		return $owner;
	}
	public static function getGithubRepoName(string $url): string{
		$repo = $url;
		$repo = QMStr::removeIfLastCharacter("/", $repo);
		if(stripos($repo, ".github.io")){
			$repo = QMStr::after(".github.io/", $repo);
			if(!$repo){
				le('!$repo');
			}
			return $repo;
		}
		$repo = QMStr::afterLast($repo, "/");
		if(!$repo){
			le('!$repo');
		}
		$repo = QMStr::before(".git", $repo, $repo);
		$repo = QMStr::before("#", $repo, $repo);
		return $repo;
	}
	abstract public function getLockPath(): string;
	public function getLock(): object{
		$path = $this->getLockPath();
		try {
			$str = FileHelper::getContents($path);
		} catch (QMFileNotFoundException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
		$obj = json_decode($str);
		return $lockFile = $obj;
	}
	public function getFilePath(): string{
		return FileHelper::absPath($this->path);
	}
}
