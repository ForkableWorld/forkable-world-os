<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CleanupUpdateButton extends AdminButton {
	public $accessibilityText = 'Cleanup-Update';
	public $action = 'admin/cleanup-update';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-cleanup-update-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/cleanup-update';
	public $target = 'self';
	public $text = 'Cleanup-Update';
	public $title = 'Cleanup-Update';
	public $tooltip = 'Run data cleaning update solution. ';
	public $visible = true;
}
