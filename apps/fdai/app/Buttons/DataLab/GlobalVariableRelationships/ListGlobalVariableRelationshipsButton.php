<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\GlobalVariableRelationships;
use App\Buttons\QMButton;
use App\Models\GlobalVariableRelationship;
class ListGlobalVariableRelationshipsButton extends QMButton {
	public $accessibilityText = 'List Global Variable Relationships';
	public $action = 'datalab/aggregateCorrelations';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'datalab-global-variable-relationships-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $link = 'datalab/aggregateCorrelations';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.aggregateCorrelations.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\GlobalVariableRelationshipController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\GlobalVariableRelationshipController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Global Variable Relationships';
	public $title = 'List Global Variable Relationships';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
	public $visible = true;
}
