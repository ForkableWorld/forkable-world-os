<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\PHPStormButton;
use App\Solutions\EditFileSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class MissingPropertyException extends \LogicException implements ProvidesSolution
{
    /**
     * @var string
     */
    private $phpStormUrl;
    private $documentationLinks;
    public function __construct(string $message, array $documentationLinks = []){
        $this->phpStormUrl = PHPStormButton::redirectUrl(debug_backtrace()[1]['file'], debug_backtrace()[1]['line']);
        $this->documentationLinks = $documentationLinks;
        parent::__construct($message, 500);
    }
    public function getSolutionActionDescription(): string{
        return "Add a default property";
    }
    public function getDocumentationLinks(): array{
        $arr = $this->documentationLinks;
        $arr['Jump to Class'] = $this->phpStormUrl;
        return $arr;
    }
    public function getSolution(): Solution{
        $s = new EditFileSolution();
        return $s;
    }
}
