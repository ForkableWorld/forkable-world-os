<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Logging\QMLog;
use App\Slim\View\Request\QMRequest;
use App\Utils\AppMode;
use App\Utils\QMProfile;
use Facade\IgnitionContracts\Solution;
use Tests\QMBaseTestCase;
class ProfileSolution extends AbstractSolution implements Solution {
	public function getSolutionTitle(): string{
		return "Check Tideways Profile";
	}
	public function getSolutionDescription(): string{
		return "Click the link below to see the profile";
	}
	public function getDocumentationLinks(): array{
		QMProfile::endProfile();
		if($url = QMProfile::getLastProfileUrl()){return ["View Profile" => $url];}
		if(AppMode::isApiRequest()){
			return ["Profile API Request" => QMRequest::current([QMRequest::PARAM_PROFILE => 1])];
		}
		le("Please implement ".__METHOD__." for this situation");
	}
}
