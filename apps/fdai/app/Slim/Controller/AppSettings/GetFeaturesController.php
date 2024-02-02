<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\AppSettings;
use App\AppSettings\AppDesign\FeaturesListSettings;
use App\Slim\Controller\GetController;
class GetFeaturesController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		return $this->writeJsonWithGlobalFields(200, FeaturesListSettings::getDefaultFeaturesList());
	}
}
