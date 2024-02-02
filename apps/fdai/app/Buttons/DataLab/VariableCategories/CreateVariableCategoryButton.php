<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\VariableCategories;
use App\Buttons\QMButton;
use App\Models\VariableCategory;
class CreateVariableCategoryButton extends QMButton {
	public $accessibilityText = 'Create Variable Category';
	public $action = 'datalab/variableCategories/create';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'datalab-variable-categories-create-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $link = 'datalab/variableCategories/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.variableCategories.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VariableCategoryController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VariableCategoryController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Variable Category';
	public $title = 'Create Variable Category';
	public $tooltip = VariableCategory::CLASS_DESCRIPTION;
	public $visible = true;
}
