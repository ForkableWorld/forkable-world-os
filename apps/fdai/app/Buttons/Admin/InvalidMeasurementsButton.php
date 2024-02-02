<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class InvalidMeasurementsButton extends AdminButton {
	public $accessibilityText = 'Invalid-Measurements';
	public $action = 'admin/invalid-measurements';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-invalid-measurements-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/invalid-measurements';
	public $target = 'self';
	public $text = 'Invalid-Measurements';
	public $title = 'Invalid-Measurements';
	public $tooltip = 'Admin/invalid-Measurements ' . "\n" . '            title from url: Admin Invalid-Measurements' .
	"\n" . '            title from name: ' . "\n" . '            action: ';
	public $visible = true;
}
