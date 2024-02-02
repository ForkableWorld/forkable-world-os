<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\Logging\QMLog;
use App\Slim\View\Request\QMRequest;
class RecastController extends PostController {
	public function post(){
		$this->setCacheControlHeader(5 * 60);
		$body = $this->getBody();
		if(QMRequest::urlContains('/errors')){
			QMLog::error("Recast error", $body);
		}
		$this->write([
			'replies' => [
				[
					'type' => 'text',
					'content' => 'Roger that',
				],
			],
			'conversation' => [
				'memory' => ['key' => 'value'],
			],
		]);
	}
}
