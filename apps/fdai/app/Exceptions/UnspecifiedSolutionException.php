<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\PHPStormButton;
use App\Logging\SolutionButton;
use App\Solutions\CreateException;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;
class UnspecifiedSolutionException extends \Exception implements ProvidesSolution {
    /**
     * @var string
     */
    public $exceptionClassWithoutSolution;
    /**
     * @var Throwable
     */
    public $exceptionWithoutSolution;
    public function __construct(Throwable $previous, array $meta = null){
        $this->exceptionClassWithoutSolution = get_class($previous);
        $this->exceptionWithoutSolution = $previous;
        if($meta){$this->meta = $meta;}
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }
    /**
     * @return CreateException
     */
    public function getSolution(): Solution{
        $s = new CreateException($this);
        return $s;
    }
    public function getUrlToInstantiation(): string{
        return PHPStormButton::redirectUrl($this->getPrevious()->getFile(),
                                           $this->getPrevious()->getLine());
    }
    public function getDocumentationLinks(): array{
        $links["Create Custom Exception"] = $this->getSolution()->getRunUrl();
	    return SolutionButton::addUrlNameArrays($links);
    }
}
