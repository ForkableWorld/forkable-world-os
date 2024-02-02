<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\RoleBased;
use App\Menus\QMMenu;
use App\Models\Application;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\SentEmail;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class YourDataMenu extends QMMenu {
	public $badge;
	public $expanded = true;
	public $fontAwesome = FontAwesome::USER;
	public $image = ImageUrls::USER;
	public $ionIcon = IonIcon::person;
	public $rememberMenuState = true;
	public $title = "Your Data";
	public $tooltip = "See your measurements, reminders, and analyses.";
	public function getTitleAttribute(): string{ return $this->title; }
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = Measurement::getAstralIndexButton();
		$buttons[] = UserVariable::getAstralIndexButton();
		$buttons[] = TrackingReminder::getAstralIndexButton();
		$buttons[] = TrackingReminderNotification::getAstralIndexButton();
		$buttons[] = UserVariableRelationship::getAstralIndexButton();
		$buttons[] = Connection::getAstralIndexButton();
		$buttons[] = ConnectorImport::getAstralIndexButton();
		$buttons[] = SentEmail::getAstralIndexButton();
		$buttons[] = Application::getAstralIndexButton();
		$this->addButtons($buttons);
		return $buttons;
	}
}
