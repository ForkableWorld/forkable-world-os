<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\UserTags;
use App\Buttons\QMButton;
use App\Models\UserTag;
class ListUserTagsButton extends QMButton {
	public $accessibilityText = 'List User Tags';
	public $action = 'datalab/userTags';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'datalab-user-tags-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $link = 'datalab/userTags';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.userTags.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserTagController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserTagController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List User Tags';
	public $title = 'List User Tags';
	public $tooltip = UserTag::CLASS_DESCRIPTION;
	public $visible = true;
}
