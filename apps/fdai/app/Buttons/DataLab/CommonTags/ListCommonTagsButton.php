<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\CommonTags;
use App\Buttons\QMButton;
use App\Models\CommonTag;
class ListCommonTagsButton extends QMButton {
	public $accessibilityText = 'List Common Tags';
	public $action = 'datalab/commonTags';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'datalab-common-tags-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $link = 'datalab/commonTags';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.commonTags.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\CommonTagController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\CommonTagController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Common Tags';
	public $title = 'List Common Tags';
	public $tooltip = CommonTag::CLASS_DESCRIPTION;
	public $visible = true;
}
