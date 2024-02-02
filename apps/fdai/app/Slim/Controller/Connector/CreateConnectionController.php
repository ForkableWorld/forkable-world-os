<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
class CreateConnectionController extends PostController {
	public function post(){
		QMConnector::fromRequest()->connect(QMRequest::body());
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 'OK',
			'user' => QMAuth::getQMUserIfSet(),
			'connectors' => QMDataSource::getForRequest(),
		]);
	}
}
