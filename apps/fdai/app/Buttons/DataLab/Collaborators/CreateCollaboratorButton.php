<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Collaborators;
use App\Buttons\QMButton;
use App\Models\Collaborator;
class CreateCollaboratorButton extends QMButton {
	public $accessibilityText = 'Create Collaborator';
	public $action = 'datalab/collaborators/create';
	public $color = Collaborator::COLOR;
	public $fontAwesome = Collaborator::FONT_AWESOME;
	public $id = 'datalab-collaborators-create-button';
	public $image = Collaborator::DEFAULT_IMAGE;
	public $link = 'datalab/collaborators/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.collaborators.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\CollaboratorController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\CollaboratorController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Collaborator';
	public $title = 'Create Collaborator';
	public $tooltip = Collaborator::CLASS_DESCRIPTION;
	public $visible = true;
}
