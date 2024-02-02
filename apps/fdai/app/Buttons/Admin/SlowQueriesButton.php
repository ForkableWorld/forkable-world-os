<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class SlowQueriesButton extends AdminButton {
	public $accessibilityText = 'Slow-Queries';
	public $action = 'admin/slow-queries';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-slow-queries-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/slow-queries';
	public $target = 'self';
	public $text = 'Slow-Queries';
	public $title = 'Slow-Queries';
	public $tooltip = 'Slow-Queries ' . "\n" . '            title from url: Admin Slow-Queries' . "\n" .
	'            title from name: Slow-queries' . "\n" . '            action: ';
	public $visible = true;
}
