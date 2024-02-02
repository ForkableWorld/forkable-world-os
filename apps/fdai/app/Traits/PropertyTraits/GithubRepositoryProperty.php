<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasGithubRepository;
use App\Models\GithubRepository;
trait GithubRepositoryProperty
{
    use HasGithubRepository;
    public function getGithubRepositoryId(): int{
        return $this->getParentModel()->getId();
    }
    public function getGithubRepository(): GithubRepository{
        return $this->getParentModel();
    }
}
