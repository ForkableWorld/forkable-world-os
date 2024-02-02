<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\UserTags;
use App\Buttons\QMButton;
use App\Models\UserTag;
class CreateUserTagButton extends QMButton {
	public $accessibilityText = 'Create User Tag';
	public $action = 'datalab/userTags/create';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'datalab-user-tags-create-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $link = 'datalab/userTags/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.userTags.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserTagController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserTagController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create User Tag';
	public $title = 'Create User Tag';
	public $tooltip = UserTag::CLASS_DESCRIPTION;
	public $visible = true;
}
