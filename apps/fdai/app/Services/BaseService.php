<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use Illuminate\Database\Connection;
use Illuminate\Log\Logger;
class BaseService {
    /** @var Connection */
    protected $connection;
    /** @var Logger */
    protected $logger;
    public function __construct(Connection $connection, Logger $logger){
        $this->connection = $connection;
        $this->logger = $logger;
    }
}
