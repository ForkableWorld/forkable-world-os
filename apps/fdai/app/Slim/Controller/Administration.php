<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\DevOps\GithubHelper;
use App\Slim\QMSlim;
/** Administration management
 * @package App\Slim\Controller
 */
class Administration {
	/**
	 * Error constants.
	 */
	public const MISSING_PARAMETER_MODIFIED_VARIABLE = 'Parameter "modifiedVariable" is required';
	public function ionicMasterMerge(){
		$request = QMSlim::getInstance()->request();
		$response = GithubHelper::githubDevelopBranchBuildStatus($request->get('server'));
		QMSlim::getInstance()->writeJsonWithGlobalFields(200, $response);
	}
}
