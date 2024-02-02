<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\DataSources\Connectors\Responses\Github\Repo;
use App\Exceptions\CredentialsNotFoundException;
use App\Files\FileHelper;
use App\Models\User;
use App\Repos\GitRepo;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Token\Exception\ExpiredTokenException;
class GithubButton extends QMButton {
	public $image = ImageUrls::DATA_SOURCES_GITHUB_SMALL_MFUESC;
	public $fontAwesome = FontAwesome::GITHUB;
	const BUTTONS_FOLDER = parent::BUTTONS_FOLDER . '/Github';
	/**
	 * @throws CredentialsNotFoundException
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	public static function generate(){
		$fromGithub = User::mike()->github()->getRepositories();
		$classes = FileHelper::getClassesInFolder('DevOps/Repos');
		foreach($classes as $class){
			/** @var GitRepo $class */
			/** @var GitRepo $repoModel */
			$repoModel = new $class();
			/** @var Repo $repo */
			$repo = $fromGithub[$class::getOwnerRepo()] ?? GitRepo::getRepoResponseObject();
			if($repo->fork){
				continue;
			}
			$b = new static();
			$b->setTextAndTitle(QMStr::titleCaseSlow(str_replace('-', ' ', $repo->name)));
			if($repo->description){
				$b->setTooltip($repo->description);
			}
			$b->setImage($repo->owner->avatar_url);
			$b->setUrl($repo->url);
			$b->saveHardCodedModel();
		}
	}
	public static function getHardCodedDirectory(): string{
		return static::BUTTONS_FOLDER;
	}
	protected function getHardCodedShortClassName(): string{
		$class = QMStr::toClassName($this->getTitleAttribute()) . $this->getShortClassName();
		return $class;
	}
}
