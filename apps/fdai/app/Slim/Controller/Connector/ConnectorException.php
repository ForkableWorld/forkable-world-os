<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpPropertyOnlyWrittenInspection */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
use App\Logging\QMLog;
use App\Models\Connector;
use App\Slim\Model\QMResponseBody;
class ConnectorException extends \Exception {
	public $success = false;
	public $status = QMResponseBody::STATUS_ERROR;
	public $errorTitle;
	public $errorMessage;
	public ?string $userMessage;
	private QMConnector $connector;
	/**
	 * @param QMConnector|Connector $connector
	 * @param string $methodName
	 * @param int $code
	 * @param string $errorTitle
	 * @param string $errorMessage
	 * @param string|null $userMessage
	 */
	public function __construct($connector, string $methodName, int $code, string $errorTitle,
		string|array $errorMessage, string $userMessage = null){
		if(is_array($errorMessage)){
			$errorMessage = QMLog::print($errorMessage, "", true);
		}
		$this->errorTitle = $errorTitle;
		$connectUrlWithParams = $connector->getConnectUrlWithParams();
		$this->userMessage = $this->errorMessage = $userMessage ?? 
		                                           "\n\tTry reconnecting at ".str_replace("testing.",
		                                                                                                  "app.",
		                                                                                  $connectUrlWithParams);
		$this->connector = $connector;
		$combined = $this->errorTitle . "\n" . $this->errorMessage;
		//$connector->logError($combined);
		parent::__construct($combined, $code);
	}
	/**
	 * @return string|null
	 */
	public function getUserMessage(): string{
		return $this->userMessage ?? $this->errorMessage;
	}
}
