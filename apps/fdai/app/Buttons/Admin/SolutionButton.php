<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class SolutionButton extends AdminButton {
	public $accessibilityText = 'Solution';
	public $action = 'admin/solution';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-solution-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/solution';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'uses' => 'App\\Http\\Controllers\\Admin\\SolutionController@runSolution',
		'controller' => 'App\\Http\\Controllers\\Admin\\SolutionController@runSolution',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
		'as' => 'admin.solution',
	];
	public $target = 'self';
	public $text = 'Solution';
	public $title = 'Solution';
	public $tooltip = 'Admin Solution ' . "\n" . '            title from url: Admin Solution' . "\n" .
	'            title from name: Admin solution' . "\n" . '            action: ';
	public $visible = true;
}
