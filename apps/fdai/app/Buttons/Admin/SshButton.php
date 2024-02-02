<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class SshButton extends AdminButton {
	public $accessibilityText = 'Ssh';
	public $action = 'admin/ssh';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-crosshairs';
	public $id = 'admin-ssh-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/ssh';
	public $target = 'self';
	public $text = 'Ssh';
	public $title = 'Ssh';
	public $tooltip = 'Ssh ' . "\n" . '            title from url: Admin Ssh' . "\n" .
	'            title from name: Ssh' . "\n" . '            action: ';
	public $visible = true;
}
