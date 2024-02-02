<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\PHPStormButton;
use App\Traits\HasBaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
class InvalidDatabaseCredentialsException extends \Exception implements ProvidesSolution
{
    use HasBaseSolution;
    /**
     * InvalidDatabaseCredentialsException constructor.
     * @param string $url
     */
    public function __construct(string $url){
        parent::__construct("Could not connect to $url");
    }
    public function getSolutionTitle(): string{
        return "Fix DB Credentials";
    }
    public function getSolutionDescription(): string{
        return "Fix them in the .env file";
    }
    public function getDocumentationLinks(): array{
        return [
            ".env" => PHPStormButton::redirectUrl(".env")
        ];
    }
}
