<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\VariableCategories;
use App\Buttons\QMButton;
use App\Models\VariableCategory;
class ListVariableCategoriesButton extends QMButton {
	public $accessibilityText = 'List Variable Categories';
	public $action = 'datalab/variableCategories';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'datalab-variable-categories-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $link = 'datalab/variableCategories';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.variableCategories.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VariableCategoryController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VariableCategoryController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Variable Categories';
	public $title = 'List Variable Categories';
	public $tooltip = VariableCategory::CLASS_DESCRIPTION;
	public $visible = true;
}
