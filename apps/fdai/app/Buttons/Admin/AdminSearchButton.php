<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class AdminSearchButton extends AdminButton {
	public $accessibilityText = 'Admin Search Button';
	public $action = 'admin/search';
	public $color = '#3467d6';
	public $fontAwesome = 'fab fa-researchgate';
	public $id = 'admin-search-button';
	public $image = 'https://static.quantimo.do/img/basic-flat-icons/png/search.png';
	public $link = '/admin/search';
	public $target = 'self';
	public $text = 'Admin Search';
	public $title = 'Admin Search';
	public $tooltip = 'Search for administrative pages';
	public $visible = true;
}
