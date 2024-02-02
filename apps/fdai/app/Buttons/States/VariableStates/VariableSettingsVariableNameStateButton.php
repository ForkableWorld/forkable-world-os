<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
use App\Variables\QMVariable;
class VariableSettingsVariableNameStateButton extends VariableDependentStateButton {
	public $action = '/#/app/variable-settings/:variableName';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-settings';
	public $id = 'variable-settings-variable-name-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/variable-list-screenshot-caption.png';
	public $ionIcon = 'ion-settings';
	public $link = '/#/app/variable-settings/:variableName';
	public $stateName = 'app.variableSettingsVariableName';
	public $stateParams = [];
	public $text = 'Variable Settings';
	public $title = 'Variable Settings';
	public $menus = [];
	/**
	 * @param string|QMVariable $variableName
	 */
	public function __construct($variableName){
		parent::__construct();
		$displayName = $variableName;
		if(!is_string($variableName)){
			$displayName = $variableName->getTitleAttribute();
			$variableName = $variableName->getNameAttribute();
		}
		$this->title = $displayName . " Settings";
		$this->link = str_replace(":variableName", $variableName, $this->link);
	}
}
