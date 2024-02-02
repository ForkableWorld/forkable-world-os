<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\Models\Study;
use App\Studies\QMStudy;
use App\Utils\APIHelper;
use App\Utils\QMProfile;
/** Class GetStudyController
 * @package App\Slim\Controller\Study
 */
class GetStudyController extends GetStudiesController {
	public static $userStudyError;
	public function get(){
		QMProfile::endProfile();
		QMProfile::profileIfEnvSet(false, false,__METHOD__);
		$this->setCacheControlHeader(86400 * 60);
		$study = Study::findOrNewByRequest();
		$dbm = $study->getDBModel();
		/** @var QMStudy $study */
		$arr = $dbm->prepareResponse();
		if(APIHelper::apiVersionIsAbove(4)){
			$arr = ['study' => $arr];
		}
		$this->writeJsonWithoutGlobalFields(200, $arr);
		QMProfile::endProfile();
	}
}
