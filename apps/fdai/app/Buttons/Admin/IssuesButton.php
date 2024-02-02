<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class IssuesButton extends AdminButton {
	public $accessibilityText = 'Github Issues';
	public $action = 'admin/issues';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-issues-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/issues';
	public $parameters = [];
	public $target = 'self';
	public $text = 'Issues';
	public $title = 'Issues';
	public $tooltip = 'Github Issues';
	public $visible = true;
}
