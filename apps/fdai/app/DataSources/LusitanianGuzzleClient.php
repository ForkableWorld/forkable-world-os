<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace App\DataSources;
use App\Exceptions\ModelValidationException;
use App\Logging\ConsoleLog;
use App\Logging\QMClockwork;
use App\Models\ConnectorRequest;
use App\Slim\Controller\Connector\ConnectorException;
use App\Types\QMStr;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Profiling\Clockwork\Profiler;
use GuzzleHttp\Psr7\Response;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Uri\UriInterface;
use Psr\Http\Message\ResponseInterface;
/**
 * @method static Response get(string|\Psr\Http\Message\UriInterface $uri, array $options = [])
 * @method static Response head(string|UriInterface $uri, array $options = [])
 * @method static Response put(string|UriInterface $uri, array $options = [])
 * @method static Response post(string|UriInterface $uri, array $options = [])
 * @method static Response patch(string|UriInterface $uri, array $options = [])
 * @method static Response delete(string|UriInterface $uri, array $options = [])
 */
class LusitanianGuzzleClient extends Client implements ClientInterface {
	private array $connectorRequests = [];
	private string $contents;
	private ResponseInterface $lastResponse;
	private ?QMConnector $qmConnector = null;
	private array $requestContainer;
	/**
	 * @param array $config
	 * @param \App\DataSources\QMConnector|\App\Scrapers\BaseScraper $c
	 */
	public function __construct(array $config = [], $c = null){
		if($c instanceof QMConnector){
			$this->qmConnector = $c;
		}
		// $config['debug'] = \App\Utils\Env::get('APP_DEBUG');
		$this->requestContainer = [];
		$stack = HandlerStack::create();
		$stack->push(Middleware::history($this->requestContainer));  // Add the history middleware to the handler stack.
		if(QMClockwork::enabled()){
			$stack->unshift(new \GuzzleHttp\Profiling\Middleware(new Profiler(QMClockwork::clock()->timeline())));
		}
		// http://docs.guzzlephp.org/en/stable/quickstart.html
		$config['handler'] = $stack; // or $handlerStack = HandlerStack::create($mock); if using the Mock handler.
		$config['cookies'] = true; // See http://docs.guzzlephp.org/en/stable/quickstart.html#cookies
		$config['verify'] = false; // https://stackoverflow.com/questions/35638497/curl-error-60-ssl-certificate-prblm-unable-to-get-local-issuer-certificate
		parent::__construct($config);
	}
	/**
	 * @return ConnectorRequest[]
	 */
	public function getConnectorRequests(): array{
		return $this->connectorRequests;
	}
	/**
	 * @return string
	 */
	public function getContents(): string{
		return $this->contents;
	}
	/**
	 * @return ResponseInterface
	 */
	public function getLastResponse(): ResponseInterface{
		return $this->lastResponse;
	}
	/**
	 * @return QMConnector|null
	 */
	public function getQmConnector(): ?QMConnector{
		return $this->qmConnector;
	}
	/**
	 * @param UriInterface $endpoint
	 * @param mixed $requestBody
	 * @param array $extraHeaders
	 * @param string $method
	 * @return string
	 * @throws ConnectorException
	 */
	public function retrieveResponse(UriInterface $endpoint, $requestBody, array $extraHeaders = [],
		$method = 'POST'): string{
		if($this->getConfig('debug')){
			le(__METHOD__.": Don't use this method in debug mode because it causes a weird error: 
			curl_setopt_array(): Cannot represent a stream of type Output as a STDIO FILE* ");
		}
		try {
			$r = $this->lastResponse = $this->request($method, $endpoint->getAbsoluteUri(), [
				'headers' => $extraHeaders,
				'form_params' => $requestBody,
			]);
		} /** @noinspection PhpMultipleClassDeclarationsInspection */ catch (ClientException | GuzzleException $e) {
			$r = $e->getResponse();
			if(!$r){
				le("Could not get response from this " . get_class($e) . ": " . $e->getMessage() . "\n" .
					\App\Logging\QMLog::print_r($e, true));
			}
			$this->lastResponse = $r;
		}
		$body = $r->getBody();
        $decoded = json_decode($body, true);
		$contents = $body->getContents();
		if(empty($contents)){
			$contents = $body;
		}
		if(isset($decoded['status']) && is_int($decoded['status'])){
			$code = $decoded['status'];
		} else {
			$code = $r->getStatusCode();
		}
        $contents = $decoded["error"] ?? $contents;
		$cr = new ConnectorRequest([
			ConnectorRequest::FIELD_URI => $endpoint->getAbsoluteUri(),
			ConnectorRequest::FIELD_REQUEST_HEADERS => $extraHeaders,
			ConnectorRequest::FIELD_METHOD => $method,
			ConnectorRequest::FIELD_RESPONSE_BODY => QMStr::jsonEncodeIfNecessary($contents),
			ConnectorRequest::FIELD_CODE => $code,
			ConnectorRequest::FIELD_CREATED_AT => now_at(),
		]);
		if($requestBody){
			$cr->request_body = $requestBody;
		}
		$this->connectorRequests[] = $cr;
		$this->saveConnectorRequests();
		if(isset($e)){
			$reason = $r->getReasonPhrase();
			throw new ConnectorException($this->qmConnector, $method, $e->getCode(),
				"$reason response from $endpoint",
				$contents);
		}
		// $this->contents is necessary because it's erased from the response for some reason the first time you
		// get them.
		return $this->contents = $contents;
	}
	/**
	 * Handle dynamic static method calls into the method.
	 * @param string $method
	 * @param array $parameters
	 * @return Response
	 */
	public static function __callStatic(string $method, array $parameters){
		$uri = $parameters[0];
		$options = $parameters[1];
		if($method === "post"){
			if(!isset($options['form_params'])){
				$options = ['form_params' => $options];
			}
		}
		ConsoleLog::info("$method $uri", $parameters);
		$i = new static();
		/** @var Response $r */
		$r = $i->$method($uri, $options);
		ConsoleLog::info($r->getStatusCode() . " response from $method $uri");
		return $r;
	}
	/**
	 * @return ConnectorRequest[]
	 */
	public function saveConnectorRequests(): array{
		$requests = $this->getConnectorRequests();
		foreach($requests as $cr){
			if($cr->id){
				continue;
			}
			if($c = $this->qmConnector){
				$cr->connector_id = $c->id;
				if($c->userId){
					$cr->user_id = $c->userId;
					$cr->connection_id = $c->getConnection()->getId();
					$cr->connector_import_id = $c->getOrCreateConnection()->getConnectorImport()->id;
					try {
						$cr->save();
					} catch (ModelValidationException $e) {
						le($e);
					}
				}
			}
		}
		return $requests;
	}
}
