<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Computers\ThisComputer;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Repos\QMAPIRepo;
use App\Types\QMStr;
use App\UI\ImageUrls;
class ComposerJson extends PackageList {
	public $autoload;
	public $config;
	public $extra;
	public $minimumStability;
	public $preferStable;
	public $repositories;
	public $require;
	public $requireDev;
	public $languageImage = ImageUrls::PHP;
	public $resumeHeading = "PHP Experience";
	public function __construct(string $path = 'composer.json'){
		parent::__construct($path);
	}
	protected function getDependencies(): array{
		$repos = [];
		foreach($this->require as $ownerRepo => $version){
			$repos[] = $ownerRepo;
		}
		$dev = $this->requireDev;
		if(!$dev){
			le('!$dev');
		}
		foreach($dev as $ownerRepo => $version){
			$repos[] = $ownerRepo;
		}
		return $repos;
	}
	public function getLockPath(): string{
		return str_replace('.json', '.lock', $this->path);
	}
	public function getLockPackages(): array{
		return $this->getLock()->packages;
	}
	public function getGithubUrl(string $ownerRepo): string{
		$packages = $this->getLockPackages();
		foreach($packages as $package){
			if($package->name === $ownerRepo && $package->source->url){
				return $package->source->url;
			}
		}
		throw new NotFoundException("Could not find url for $ownerRepo");
	}
	public static function instance(): self{
		return new static();
	}
	public static function removeUsedDependenciesAndCommit(){
		$c = new static();
		$all = $c->getDependencies();
		foreach($all as $one){
			QMAPIRepo::createFeatureBranch(QMStr::slugify($one));
			try {
				FileHelper::removeLinesContaining($c->getFilePath(), $one);
			} catch (QMFileNotFoundException $e) {
				le($e);
			}
			$c->update();
			QMAPIRepo::addAllCommitAndPush($one);
		}
	}
	public function update(){
		$path = $this->getFilePath();
		ThisComputer::exec("cd $path && composer update");
	}
}
