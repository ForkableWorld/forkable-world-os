<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Feed;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
class GetUserFeedController extends GetController {
	/**
	 * @return \Illuminate\Http\Response
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function get(){
		$this->setCacheControlHeader(60);
		return $this->writeJsonWithGlobalFields(200,
			new UserFeedResponse(QMAuth::getAuthenticatedUserOrThrowException()->getUser(), null));
	}
}
