<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\GlobalVariableRelationships;
use App\Buttons\QMButton;
use App\Models\GlobalVariableRelationship;
class CreateGlobalVariableRelationshipButton extends QMButton {
	public $accessibilityText = 'Create Global Variable Relationship';
	public $action = 'datalab/aggregateCorrelations/create';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'datalab-global-variable-relationships-create-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $link = 'datalab/aggregateCorrelations/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.aggregateCorrelations.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\GlobalVariableRelationshipController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\GlobalVariableRelationshipController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Global Variable Relationship';
	public $title = 'Create Global Variable Relationship';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
	public $visible = true;
}
