<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class OverTimeButton extends AdminButton {
	public $accessibilityText = 'Over-Time';
	public $action = 'admin/over-time';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-over-time-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/over-time';
	public $target = 'self';
	public $text = 'Over-Time';
	public $title = 'Over-Time';
	public $tooltip = 'Over-Time ' . "\n" . '            title from url: Admin Over-Time' . "\n" .
	'            title from name: Over-time' . "\n" . '            action: ';
	public $visible = true;
}
