<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class GenerateScaffoldButton extends AdminButton {
	public $accessibilityText = 'generate Scaffold';
	public $action = 'admin/generateScaffold';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-generate-scaffold-button';
	public $image = 'https://static.quantimo.do/img/jetbrains/modules/generatedSourceRoot.png~';
	public $link = '/admin/generateScaffold';
	public $target = 'self';
	public $text = 'generate Scaffold';
	public $title = 'generate Scaffold';
	public $tooltip = 'Admin generateScaffold ' . "\n" . '            title from url: Admin generateScaffold' . "\n" .
	'            title from name: Admin generate Scaffold' . "\n" . '            action: ';
	public $visible = true;
}
