<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\User;
use App\Solutions\QMBaseSolution;
use App\Traits\HasBaseSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use App\Slim\Model\User\QMUser;
use Facade\IgnitionContracts\Solution;
class NoTimeZoneException extends Exception implements ProvidesSolution
{
    use HasBaseSolution;
    /**
     * @var QMUser|User
     */
    private $user;
    /**
     * NoTimeZoneException constructor.
     * @param User|QMUser $u
     * @param string $message
     */
    public function __construct($u, string $message = ""){
        $this->user = $u;
        parent::__construct("$message No time zone abbreviation or offset");
    }
    /**
     * @return User
     */
    public function getUser(): User{
        return $this->user->l();
    }
    public function getSolutionTitle(): string{
        return "Inspect user data";
    }
    public function getSolutionDescription(): string{
        return "See if we can figure out time zone somehow";
    }
    public function getDocumentationLinks(): array{
        $u = $this->getUser();
        return [$u->getTitleAttribute() => $u->getUrl()];
    }
	public function getSolution(): Solution{
		return (new QMBaseSolution($this->getSolutionTitle()))
			->setSolutionDescription($this->getSolutionDescription())
			->setDocumentationLinks($this->getDocumentationLinks());
	}
}
