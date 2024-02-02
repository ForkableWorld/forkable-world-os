<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\GithubRepository;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasGithubRepository
{
    public function getGithubRepositoryId(): int {
        $nameOrId = $this->getAttribute('github_repository_id');
        return $nameOrId;
    }
    public function getGithubRepositoryButton(): QMButton {
        $githubRepository = $this->getGithubRepository();
        if($githubRepository){
            return $githubRepository->getButton();
        }
        return GithubRepository::generateShowButton($this->getGithubRepositoryId());
    }
    /**
     * @return GithubRepository
     */
    public function getGithubRepository(): GithubRepository {
        if($this instanceof BaseProperty && $this->parentModel instanceof GithubRepository){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('github_repository')){return $l;}
        $id = $this->getGithubRepositoryId();
        $githubRepository = GithubRepository::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['github_repository'] = $githubRepository; }
        if(property_exists($this, 'githubRepository')){
            $this->githubRepository = $githubRepository;
        }
        return $githubRepository;
    }
}
