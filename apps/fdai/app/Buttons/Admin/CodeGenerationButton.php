<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CodeGenerationButton extends AdminButton {
	public $accessibilityText = 'Code Generation Controller';
	public $action = 'admin/code Generation/controller';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-code Generation-controller-button';
	public $image = 'https://static.quantimo.do/img/activities/png/game-controller.png';
	public $link = '/admin/code-generation/controller';
	public $parameters = [];
	public $target = 'self';
	public $text = 'Code Generation Controller';
	public $title = 'Code Generation Controller';
	public $tooltip = 'Automated generation of controller code. ';
	public $visible = true;
}
