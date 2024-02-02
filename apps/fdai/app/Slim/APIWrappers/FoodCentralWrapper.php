<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\APIWrappers;
use GuzzleHttp\Client;
class FoodCentralWrapper {

	public static function search(string $query){
		$client = new Client();
		$form_params = [
			'json' => [
				'Survey (FNDDS)' => true,
				'Foundation' => true,
				'Branded' => true,
				'SR Legacy' => true,
			],
			'referenceFoodsCheckBox' => true,
			'sortCriteria' => [
				'sortColumn' => 'description',
				'sortDirection' => 'asc',
			],
			'generalSearchInput' => $query,
			'pageNumber' => 1,
			'exactBrandOwner' => null,
		];
		$response =
			$client->get('https://api.nal.usda.gov/fdc/v1/search?api_key=' . getenv('FOOD_CENTRAL_API_KEY') . "&generalSearchInput=" .
				urlencode($query) . "&requireAllWords=true");
		$statusCode = $response->getStatusCode();
		$body = $response->getBody()->getContents();
		//return $body;
		return json_decode($body);
	}
}
