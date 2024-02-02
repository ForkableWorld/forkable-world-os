<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\HasBaseSolution;
use App\Utils\ReleaseStage;
use Facade\IgnitionContracts\ProvidesSolution;
use App\Utils\Env;
use Predis\Connection\ConnectionException;
class InvalidRedisCredentialsException extends \Exception implements ProvidesSolution
{
    use HasBaseSolution;
    public function __construct(ConnectionException $previous){
        parent::__construct($previous->getMessage(), 500, $previous);
    }
    public function getSolutionTitle(): string{
        return "Update Redis Credentials or Make Sure the Lightsail Port Open";
    }
    public function getSolutionDescription(): string{
        $stage = ReleaseStage::getReleaseStage();
        return "Check the Networking tab on the Lightsail production web server and ".
            "make sure port 6379 is open for your IP.  Or if you're using Heroku,".
            " Redis credentials change periodically. So go to Heroku and ".
            "check the credentials for qm-$stage and update them in .env if necessary. ";
    }
    public function getDocumentationLinks(): array{
        return [
            "Check the Networking tab on the Lightsail production web server"=> "https://lightsail.aws.amazon.com/",
            "Redis on Heroku (Deprecated because I think we just use redis on the production web server)" => "https://data.heroku.com/",
        ];
    }
}
