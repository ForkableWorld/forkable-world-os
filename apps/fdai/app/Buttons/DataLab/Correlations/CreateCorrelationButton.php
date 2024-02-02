<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Correlations;
use App\Buttons\QMButton;
use App\Models\UserVariableRelationship;
class CreateCorrelationButton extends QMButton {
	public $accessibilityText = 'Create UserVariableRelationship';
	public $action = 'datalab/user_variable_relationships/create';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'datalab-user_variable_relationships-create-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $link = 'datalab/user_variable_relationships/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.user_variable_relationships.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\CorrelationController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\CorrelationController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create UserVariableRelationship';
	public $title = 'Create UserVariableRelationship';
	public $tooltip = UserVariableRelationship::CLASS_DESCRIPTION;
	public $visible = true;
}
