<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\CommonTags;
use App\Buttons\QMButton;
use App\Models\CommonTag;
class CreateCommonTagButton extends QMButton {
	public $accessibilityText = 'Create Common Tag';
	public $action = 'datalab/commonTags/create';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'datalab-common-tags-create-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $link = 'datalab/commonTags/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.commonTags.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\CommonTagController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\CommonTagController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Common Tag';
	public $title = 'Create Common Tag';
	public $tooltip = CommonTag::CLASS_DESCRIPTION;
	public $visible = true;
}
