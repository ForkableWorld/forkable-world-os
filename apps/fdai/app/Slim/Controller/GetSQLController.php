<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
class GetSQLController extends GetController {
	public function get(){
		if(!QMAuth::isAdmin()){
			throw new UnauthorizedException("Not authorized");
		}
		$sql = QMRequest::getParam('sql');
		$result = Writable::db()->raw($sql);
		if(!$result){
			throw new BadRequestException("$sql returned false");
		}
		return $this->writeJsonWithGlobalFields(204, ['result' => $result]);
	}
}
