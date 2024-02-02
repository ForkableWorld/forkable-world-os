<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CleanupButton extends AdminButton {
	public $accessibilityText = 'Cleanup';
	public $action = 'admin/cleanup';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-cleanup-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/cleanup';
	public $target = 'self';
	public $text = 'Cleanup';
	public $title = 'Cleanup';
	public $tooltip = 'Run automated data cleaning solutions. ';
	public $visible = true;
}
