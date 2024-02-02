<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class EnvFileButton extends AdminButton {
	public $accessibilityText = 'Env-File';
	public $action = 'admin/env-file';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-env-file-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/env-file';
	public $target = 'self';
	public $text = 'Env-File';
	public $title = 'Env-File';
	public $tooltip = 'Admin Env-File ' . "\n" . '            title from url: Admin Env-File' . "\n" .
	'            title from name: Admin env-file' . "\n" . '            action: ';
	public $visible = true;
}
