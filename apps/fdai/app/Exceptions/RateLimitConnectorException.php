<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\DataSources\QMConnector;
use App\Slim\Controller\Connector\ConnectorException;
class RateLimitConnectorException extends ConnectorException {
    /**
     * @var string
     */
    public $url;
    public $response;
    public $connector;
	/**
	 * @param string $url
	 * @param $response
	 * @param \App\DataSources\QMConnector|null $connector
	 * @param string|null $method
	 * @param int $code
	 */
    public function __construct(string $url, $response, QMConnector $connector = null, string $method = null, int
    $code = QMException::CODE_RATE_LIMIT_EXCEEDED){
        $this->url = $url;
        $this->connector = $connector;
        $this->response = $response;
        parent::__construct($connector, $method, $code, "Rate Limit Reached", "Rate Limit Reached", "Rate Limit Reached: Please Try Again Later");
    }
}
