<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CleanupSelectButton extends AdminButton {
	public $accessibilityText = 'Cleanup-Select';
	public $action = 'admin/cleanup-select';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-cleanup-select-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/cleanup-select';
	public $target = 'self';
	public $text = 'Cleanup-Select';
	public $title = 'Cleanup-Select';
	public $tooltip = 'Preview invalid records to be updated or deleted during cleaning process. ';
	public $visible = true;
}
