<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\Slim\Model\APIStats;
class GetApiStatsController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		return $this->writeJsonWithGlobalFields(200, [
			'success' => true,
			'message' => 'ok',
			'apiStats' => APIStats::getApiStats(),
		]);
	}
}
