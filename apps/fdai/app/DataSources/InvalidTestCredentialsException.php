<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Models\Connection;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Utils\UrlHelper;
class InvalidTestCredentialsException extends \LogicException implements ProvidesSolution
{
    /**
     * @var Connection
     */
    private $connection;
    public function __construct(Connection $connector){
        $this->connection = $connector;
        $message = "No test credentials for $connector!  RECONNECT me at https://local.quantimo.do/import";
        parent::__construct($message, 500);
    }
    public function getSolution(): Solution{
        $url = UrlHelper::getLocalUrl('import');
        return BaseSolution::create("Reconnect $this->connection")
            ->setSolutionDescription("Need to reconnect $this->connection at $url with your own credentials so the updated ones are saved to Firebase")
            ->setDocumentationLinks([
                "Reconnect" => $url
            ]);
    }
}
