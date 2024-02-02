<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Traits\LoggerTrait;
use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use OAuth\Common\Http\Client\AbstractClient;
use OAuth\Common\Http\Uri\UriInterface;
use App\Types\ObjectHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
/**
 * Client implementation for Guzzle
 */
class GuzzleClient extends AbstractClient{
    use LoggerTrait;
    protected $client;
    protected $lastResponse;
    private $request;
    private $endpoint;
    private $requestBody;
    private $extraHeaders;
    private $method;
    /**
     * @param UriInterface $endpoint
     * @param string|resource|array|EntityBodyInterface $requestBody
     * @param array $extraHeaders
     * @param string $method
     * @return EntityBodyInterface|string
     * @throws ClientErrorResponseException
     * @throws ServerErrorResponseException
     */
    public function retrieveResponse(UriInterface $endpoint, $requestBody, array $extraHeaders = [], $method = 'POST') {
        $this->endpoint = $endpoint;
        $this->requestBody = $requestBody;
        $this->extraHeaders = $extraHeaders;
        $this->method = $method;
        foreach ($this->getSslVersions() as $sslVersion){
            $this->setRequest($sslVersion);
            $entityBodyOrString = $this->tryToMakeRequestAndGetParsedResponse();
            if($entityBodyOrString){return $entityBodyOrString;}
        }
        if($this->endpointIsLocalHost()){
            $this->setEndpointToLocalHost();
            $entityBodyOrString = $this->tryToMakeRequestAndGetParsedResponse();
            if($entityBodyOrString){return $entityBodyOrString;}
        }
        QMLog::error("No SSL versions or SSL disabled worked for " . $this->getEndpoint()->getAbsoluteUri());
        return false;
    }
    private function setEndpointToLocalHost(){
        /** @noinspection CurlSslServerSpoofingInspection */
        $extraHeaders['curl'] = [CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0];
        $this->getEndpoint()->setPort(80);
        $this->getEndpoint()->setScheme("http");
        $this->getEndpoint()->setHost(gethostname());
        $this->setRequest(null);
    }
    /**
     * @return bool
     */
    private function endpointIsLocalHost(): bool{
        $url = $this->getEndpoint()->getAbsoluteUri();
        $result = stripos($url, \App\Utils\Env::getAppUrl()) === 0;
        $result = $result || stripos($url, 'https://localhost') === 0;
        $result = $result || stripos($url, 'http://localhost') === 0;
        return $result;
    }
    /**
     * @return EntityBodyInterface|string
     * @throws ClientErrorResponseException
     * @throws ServerErrorResponseException
     */
    private function tryToMakeRequestAndGetParsedResponse(){
        try {
            $entityBodyOrString = $this->makeRequestAndGetParsedResponse();
            QMlog::debug("SSL VERSION ".$this->getRequest()->getRawHeaders()." worked on: ".
                $this->getEndpoint()->getAbsoluteUri());
            return $entityBodyOrString;
        } catch (CurlException $exception){ // TODO: why are we catching this?
            $this->logInfo($exception->getMessage());
        }
        return false;
    }
	/**
     * @return array
     */
    public function getExtraHeaders(): array{
        return $this->extraHeaders;
    }
    /**
     * @return string
     */
    public function getMethod(): string{
        return $this->method;
    }
    /**
     * @return EntityBody|string
     * @throws ClientErrorResponseException
     * @throws ServerErrorResponseException
     */
    private function makeRequestAndGetParsedResponse(){
        $this->logInfo($this->method." ".$this->getEndpoint()->getAbsoluteUri(), $this->getExtraHeaders());
        $req = $this->getRequest();
        try {
            $this->lastResponse = $req->send();
        } catch (\Throwable $e){
            QMLog::error($e->getMessage(), ['request' => $req]);
            $this->lastResponse = $req->send();
        }
        return $this->getParsedResponse();
    }
    /**
     * @return EntityBody|string
     */
    private function getParsedResponse() {
        $type = $this->getLastResponse()->getContentType();
        if(stripos($type, 'xml') !== false){
            $responseBodyAsString = $this->getLastResponse()->getBody(true);
            return ObjectHelper::convertXmlStringToJsonWithoutDashesInProperties($responseBodyAsString);
        }
        $responseBody = $this->getLastResponse()->getBody();
        return $responseBody;
    }
    /**
     * @return array
     */
    private function getSslVersions(): array
    {
        return [
            CURL_SSLVERSION_DEFAULT,
            CURL_SSLVERSION_TLSv1,
            CURL_SSLVERSION_TLSv1_0,
            CURL_SSLVERSION_TLSv1_1,
            CURL_SSLVERSION_TLSv1_2,
            CURL_SSLVERSION_SSLv2,
            CURL_SSLVERSION_SSLv3,
            null // SSL disabled
        ];
    }
    /**
     * @return Client
     */
    private function client(): Client{
        if (!isset($this->client)) {
            $this->client = new Client();
        }
        return $this->client;
    }
    /**
     * @return Response
     */
    public function getLastResponse(): Response{
        return $this->getRequest()->getResponse();
    }
    /**
     * @return EntityEnclosingRequest|Request
     */
    public function getRequest(): ?Request {
        return $this->request;
    }
    /**
     * @return UriInterface
     */
    public function getEndpoint(): UriInterface{
        return $this->endpoint;
    }
    /**
     * @return string|resource|array|EntityBodyInterface
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }
	/**
	 * @param string|null $sslVersion
	 */
    protected function setRequest(?string $sslVersion){
        $extraHeaders = $this->getExtraHeaders();
        if($sslVersion !== null) {
            $extraHeaders['curl'] = [CURLOPT_SSLVERSION => $sslVersion];
        } else {
            /** @noinspection CurlSslServerSpoofingInspection */
            $extraHeaders['curl'] = [CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0];
        }
        $this->request = $this->client()->createRequest($this->getMethod(), $this->getEndpoint(), $extraHeaders, $this->getRequestBody());
    }
    /**
     * @param ClientErrorResponseException $e
     * @param $body
     * @return string
     */
    private function getErrorMessage(ClientErrorResponseException $e, $body): string
    {
        $errorMessage = '';
        if (isset($body->error)) {
            if(!is_string($body->error)){$body->error = json_encode($body->error);}
            $errorMessage .= $body->error . ". ";
        }
        if (isset($body->errors[0])) {
            if (is_string($body->errors[0])) {
                $errorMessage .= $body->errors[0] . ". ";
            } else {
                $errorMessage .= json_encode($body->errors[0]) . ". ";
            }
        }
        if (isset($body->message)) {
            $errorMessage .= $body->message . ". ";
        }
        if ($e->getMessage()) {
            $errorMessage .= $e->getMessage() . ". ";
        }
        if (empty($errorMessage)) {
            $errorMessage = 'Could not get error message.  Use xdebug and check GuzzleClient to see what kind of exception this is.';
        }
        return $errorMessage;
    }
    /**
     * @param ClientErrorResponseException $e
     * @return string
     */
    public function logException(ClientErrorResponseException $e): string{
        $body = $e->getResponse()->getBody(true);
        $body = json_decode($body, false);
        $errorMessage = $this->getErrorMessage($e, $body);
        $code = $e->getResponse()->getStatusCode();
        if ($code !== 409) {
            QMLog::error($code . ": $errorMessage", ['endpoint' => $this->getEndpoint(), 'responseBody' => $body, 'requestBody' => $this->getRequestBody(),
                    'error message' => $errorMessage, // Put error message here in case it contains variable url preventing grouping
                    //'exception' => $e,
                    'request' => $this->getRequest()->getUrl()]
            ); // Maybe this should be handled by the connector.  For example, Github is spamming Bugsnag with lots of 409 errors that don't need to be reported
        }
        return $errorMessage;
    }
    /**
     * @param array $metaData
     * @return array
     */
    protected function getMetaData(array $metaData = []): array{
        $metaData['uri'] = $this->getEndpoint()->getAbsoluteUri();
        return $metaData;
    }
    /**
     * @return string
     */
    public function __toString() {
        $original = $this->getEndpoint()->getAbsoluteUri();
        $uri = QMStr::before('token', $original, $original);
        $uri = QMStr::before('secret', $uri, $uri);
        if($uri !== $original){$uri = $uri . "[secure]";}
        return $uri." ";
    }
}
