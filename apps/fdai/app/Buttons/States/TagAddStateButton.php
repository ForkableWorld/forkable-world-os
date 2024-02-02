<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\VariableDependentStateButton;
class TagAddStateButton extends VariableDependentStateButton {
	public $accessibilityText = 'Tag a Variable';
	public $action = '/#/app/tag-add';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-pricetag';
	public $id = 'tag-add-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/variable-list-screenshot-caption.png';
	public $ionIcon = 'ion-pricetag';
	public $link = '/#/app/tag-add';
	public $stateName = 'app.tagAdd';
	public $stateParams = [];
	public $text = 'Tag a Variable';
	public $title = 'Tag a Variable';
	public $tooltip = 'Tag a Variable';
	public $menus = [];
}
