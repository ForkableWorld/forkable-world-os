<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Collaborators;
use App\Buttons\QMButton;
use App\Models\Collaborator;
class ListCollaboratorsButton extends QMButton {
	public $accessibilityText = 'List Collaborators';
	public $action = 'datalab/collaborators';
	public $color = Collaborator::COLOR;
	public $fontAwesome = Collaborator::FONT_AWESOME;
	public $id = 'datalab-collaborators-button';
	public $image = Collaborator::DEFAULT_IMAGE;
	public $link = 'datalab/collaborators';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.collaborators.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\CollaboratorController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\CollaboratorController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Collaborators';
	public $title = 'List Collaborators';
	public $tooltip = Collaborator::CLASS_DESCRIPTION;
	public $visible = true;
}
