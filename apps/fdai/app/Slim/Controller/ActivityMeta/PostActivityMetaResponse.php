<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\ActivityMeta;
use App\Slim\Model\ActivityMeta;
use App\Slim\Model\QMResponseBody;
class PostActivityMetaResponse extends QMResponseBody {
	public $activityMetas;
	public function __construct(){
		$this->activityMetas = ActivityMeta::get();
		parent::__construct();
	}
}
