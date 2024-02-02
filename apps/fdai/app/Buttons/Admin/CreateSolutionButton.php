<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CreateSolutionButton extends AdminButton {
	public $accessibilityText = 'Create Solution';
	public $action = 'admin/create/solution';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-create-solution-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/create/solution';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'uses' => 'App\\Http\\Controllers\\CreateFileController@createSolution',
		'controller' => 'App\\Http\\Controllers\\CreateFileController@createSolution',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
		'as' => 'admin.create.solution',
	];
	public $target = 'self';
	public $text = 'Create Solution';
	public $title = 'Create Solution';
	public $tooltip = 'Automated generation of runnable exception solution classes';
	public $visible = true;
}
