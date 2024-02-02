<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
class VueToastedAction {
	public $text; //*|String|-| name of action
	public $href; //|String|`null`| url of action
	public $target; //|String|`null`| target of url
	public $icon = ['name' => FontAwesome::EXTERNAL_LINK_ALT_SOLID]; //|String|`null`| name of material for action
	public $class; //|String/Array|`null`| custom css class for the action
	public $push; //|Object |`null`|  Vue Router push parameters
	public $onClick; //Function(e,toastObject) |`null`|  onClick Function of action
	/**
	 * @param array $icon
	 * @return VueToastedAction
	 */
	public function setIcon(array $icon): VueToastedAction{
		$this->icon['name'] = $icon;
		return $this;
	}
}
