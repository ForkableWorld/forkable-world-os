<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class ExceptionButton extends AdminButton {
	public $accessibilityText = 'Exception';
	public $action = 'admin/exception';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-exception-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/exception';
	public $target = 'self';
	public $text = 'Exception';
	public $title = 'Exception';
	public $tooltip = 'Admin Exception ';
	public $visible = true;
}
