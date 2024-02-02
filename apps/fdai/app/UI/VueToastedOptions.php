<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
class VueToastedOptions {
	public $position = 'top-right'; //|String||Position of the toast container <br> **['top-right', 'top-center', 'top-left', 'bottom-right', 'bottom-center', 'bottom-left']**
	public $duration; //|Number|null|Display time of the toast in millisecond
	public $action = []; //|Object, Array|null|Add single or multiple actions to toast  [explained here](#actions)
	public $fullWidth; //|Boolean|false|Enable Full Width
	public $fitToScreen; //|Boolean|false|Fits to Screen on Full Width
	public $className; //|String, Array|null|Custom css class name of the toast
	public $containerClass; //|String, Array|null|Custom css classes for toast container
	public $Icon; //|String, Object|null|Material icon name as string.  [explained here](#icons)
	public $type; //|String|'default'| Type of the Toast  **['success', 'info', 'error']**
	public $theme; //|String|'primary'|Theme of the toast you prefer<br> **['primary', 'outline', 'bubble']**
	public $onComplete; //|Function|null|Trigger when toast is completed
	public $closeOnSwipe; //|Boolean|true|Closes the toast when the user swipes it
	public $singleton; //|Boolean|false| Only allows one toast at a time.
	public $iconPack; //|String|'material'| Icon pack type to be used <br> **['material', 'fontawesome', 'mdi']**
	/**
	 * @param mixed $action
	 * @return VueToastedOptions
	 */
	public function addAction(VueToastedAction $action){
		$this->action[] = $action;
		return $this;
	}
}
