<?php

namespace App\Web3;

use App\Files\FileHelper;
use App\Utils\Env;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
class PinataClient
{
    private $client;

    function __construct()
    {
        $this->client = new HttpClient(
	        [
		        'base_uri' => 'https://api.pinata.cloud',
		        'headers' => [
			        'pinata_api_key' => Env::get('PINATA_API_KEY'),
			        'pinata_secret_api_key' => Env::get('PINATA_SECRET_API_KEY'),
		        ],
	        ]
        );
    }
	/**
	 * @throws GuzzleException
	 */
	public function addHashToPinQueue(string $hashToPin): array
    {
        return $this->doCall('/pinning/addHashToPinQueue', 'POST', ['hashToPin' => $hashToPin]);
    }

    public function pinFileToIPFS(string $filePathOrContent, array $metadata = []): array
    {
		if(FileHelper::fileExists($filePathOrContent)){
			$contents = fopen($filePathOrContent, 'r');
		} else {
			$contents = $filePathOrContent;
		}
        return json_decode($this->client->post('/pinning/pinFileToIPFS', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => $contents,
	                'pinataMetadata' => $metadata,
                ],
            ]
        ])->getBody()->getContents(), true);
    }
	/**
	 * @throws GuzzleException
	 */
	public function pinHashToIPFS(string $hashToPin): array
    {
        return $this->doCall('/pinning/pinHashToIPFS', 'POST', ['hashToPin' => $hashToPin]);
    }

    public function pinJobs(): array
    {
        return json_decode($this->client->get('/pinning/pinJobs')->getBody()->getContents(), true);
    }
	/**
	 * @throws GuzzleException
	 */
	public function pinJSONToIPFS(array $json, array $metadata = null): array
    {
        $content = ($metadata) ? ['pinataMetadata' => $metadata, 'pinataContent' => $json] : $json;
        return $this->doCall('/pinning/pinJSONToIPFS', 'POST', $content);
    }

    public function removePinFromIPFS(string $hash): bool
    {
        $return = $this->client->post('/pinning/removePinFromIPFS', [
            \GuzzleHttp\RequestOptions::JSON => ['ipfs_pin_hash' => $hash],
        ]);

        return $return->getStatusCode() === 200;
    }

    public function userPinnedDataTotal(): array
    {
        return json_decode($this->client->get('/data/userPinnedDataTotal')->getBody()->getContents(), true);
    }

    public function userPinList(): array
    {
        return json_decode($this->client->get('/data/userPinList')->getBody()->getContents(), true);
    }
	/**
	 * @throws GuzzleException
	 */
	private function doCall(string $endpoint, string $method = 'POST', array $params = []): array
    {
        $response = $this->client->request(
            $method,
            $endpoint,
            [
                \GuzzleHttp\RequestOptions::JSON => $params,
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
