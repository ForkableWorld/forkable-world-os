<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Exceptions;

use App\Models\Connector;
use Exception;

class ConnectorDisabledException extends Exception
{
    private Connector $connector;

    /**
     * @param Connector $connector
     * @param string $message
     */
    public function __construct(Connector $connector, string $message = "")
    {
        $this->connector = $connector;
        parent::__construct("Connector ".$connector->getNameOrTitle(). " is disabled: ".$message);
    }
}
