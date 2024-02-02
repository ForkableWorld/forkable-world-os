<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\VariableDependentStateButton;
use App\Models\Variable;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\IonicHelper;
class VariableSettingsStateButton extends VariableDependentStateButton {
	public $accessibilityText = 'Variable Settings';
	public $action = '/#/app/variable-settings';
	public $fontAwesome = FontAwesome::SETTINGS;
	public $icon = ImageUrls::BASIC_FLAT_ICONS_SETTINGS;
	public $id = 'variable-settings-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/variable-list-screenshot-caption.png';
	public $ionIcon = 'ion-settings';
	public $link = '/#/app/variable-settings';
	public $stateName = 'app.variableSettings';
	public $text = 'Variable Settings';
	public $title = 'Variable Settings';
	public $tooltip = "Set min/max filters, onset delay, duration of action, ingredient tags, filling value, etc";
	/**
	 * VariableSettingsStateButton constructor.
	 * @param Variable $v
	 */
	public function __construct($v = null){
		parent::__construct($v);
		if($v){
			$this->setTextAndTitle($v->getDisplayNameAttribute() . " Analysis Settings ➤");
		}
	}
	/**
	 * @param int $variableId
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getVariableSettingsUrlForVariableId(int $variableId, array $params = [],
		string $clientIdSubDomain = null): string{
		$params['variableId'] = $variableId;
		$url = IonicHelper::getIonicAppUrl($clientIdSubDomain, IonicHelper::PATH_VARIABLE_SETTINGS, $params);
		return $url;
	}
	public function getUrl(array $params = []): string{
		$url = IonicHelper::getIonicAppUrl(null, 'variable-settings/' . urlencode($this->getVariableName()), $params);
		$this->setUrl($url);
		return $url;
	}
}
