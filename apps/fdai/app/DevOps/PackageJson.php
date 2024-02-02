<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\DevOps;
use App\Exceptions\NotFoundException;
use App\Repos\GitRepo;
use App\Storage\LocalFileCache;
use App\UI\ImageUrls;
use App\Utils\APIHelper;
class PackageJson extends PackageList {
	private static $npm;
	public $bugs;
	public $dependencies;
	public $devDependencies;
	public $directories;
	public $homepage;
	public $main;
	public $repository;
	public $scripts;
	public $languageImage = ImageUrls::JAVASCRIPT_SVG;
	public $resumeHeading = "JavaScript Experience";
	protected static $githubUrls = [];
	public function __construct(string $path = 'package.json'){
		parent::__construct($path);
	}
	public function getDependencies(): array{
		$packages = [];
		$deps = $this->dependencies;
		foreach($deps as $ownerRepo => $version){
			$packages[] = $ownerRepo;
		}
		foreach($this->devDependencies as $ownerRepo => $version){
			$packages[] = $ownerRepo;
		}
		return $packages;
	}
	public function getGithubUrl(string $packageName): string{
		if($url = static::$githubUrls[$packageName] ?? null){
			return $url;
		}
		$NPM = $this->getNPM($packageName);
		$url = $NPM->repository->url ?? null;
		if($url && stripos($url, "github") !== false){
			return $url;
		}
		$url = $this->getHomePage($packageName);
		if(stripos($url, "github") !== false){
			return static::$githubUrls[$packageName] = $url;
		}
		$q = str_replace("@", '', $packageName);
		$matches = GitRepo::search($q);
		$url = $matches[0]->url;
		if(!$url){
			throw new NotFoundException("Could not find repo like $q");
		}
		return static::$githubUrls[$packageName] = $url;
	}
	public function getLockPath(): string{
		return str_replace('package.', 'package-lock.', $this->path);
	}
	public function getLockPackages(): array{
		return $this->getLock()->packages;
	}
	public static function instance(): self{
		return new static();
	}
	/**
	 * @param string $packageName
	 * @return \stdClass
	 */
	public function getNPM(string $packageName): ?\stdClass{
		if($npm = static::$npm[$packageName] ?? null){
			return $npm;
		}
		//if($npm = LocalCache::get(__FUNCTION__."/".$packageName)){return $npm;}
		$url = "https://registry.npmjs.org/$packageName";
		$npm = APIHelper::getRequest($url);
		LocalFileCache::set(__FUNCTION__ . "/" . $packageName, $npm);
		return static::$npm[$packageName] = $npm;
	}
	public function getHomePage(string $packageName): ?string{
		return $this->getNPM($packageName)->homepage;
	}
}
