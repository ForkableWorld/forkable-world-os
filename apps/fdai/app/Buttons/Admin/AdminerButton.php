<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class AdminerButton extends DebugButton {
	const PATH = '/admin/adminer';
	public $accessibilityText = 'Adminer';
	public $action = self::PATH;
	public $color = '#3467d6';
	public $fontAwesome = FontAwesome::DATABASE_SOLID;
	public $id = 'admin-adminer-button';
	public $image = ImageUrls::DEVELOPMENT_019_DATABASES_2;
	public $link = self::PATH;
	public $parameters = [];
	public $target = 'self';
	public $text = 'Adminer';
	public $title = 'Adminer';
	public $tooltip = 'Implemented an Adminer interface which allows data administrators to use SQL queries to view, clean, and analyze your data. ';
	public $visible = true;
	protected function getPath(): string{ return static::PATH; }
}
