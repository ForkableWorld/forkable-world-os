<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Activity;
use App\Slim\Model\Activity;
use App\Slim\Model\QMResponseBody;
class PostActivityResponse extends QMResponseBody {
	public $activities;
	public function __construct(){
		$this->activities = Activity::get();
		parent::__construct();
	}
}
