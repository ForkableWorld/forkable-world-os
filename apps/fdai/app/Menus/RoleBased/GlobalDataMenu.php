<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\RoleBased;
use App\Menus\QMMenu;
use App\Models\GlobalVariableRelationship;
use App\Models\User;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Slim\Middleware\QMAuth;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class GlobalDataMenu extends QMMenu {
	public $badge;
	public $expanded = true;
	public $fontAwesome = FontAwesome::GLOBE_SOLID;
	public $image = ImageUrls::SCIENCE_EARTH_GLOBE;
	public $ionIcon = IonIcon::androidGlobe;
	public $rememberMenuState = true;
	public $title = "Global Data";
	public $tooltip = "Publicly available aggregated data and analyses. ";
	public function getTitleAttribute(): string{ return $this->title; }
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = GlobalVariableRelationship::getAstralIndexButton();
		$buttons[] = Variable::getAstralIndexButton();
		$buttons[] = VariableCategory::getAstralIndexButton();
		$buttons[] = Variable::getIndexButton()->fontAwesome(FontAwesome::STUDY)->image(ImageUrls::STUDY)
			->title("Journal of Citizen Science");
		if(QMAuth::canSeeOtherUsers()){
			$buttons[] = User::getAstralIndexButton();
		}
		$this->addButtons($buttons);
		return $buttons;
	}
}
