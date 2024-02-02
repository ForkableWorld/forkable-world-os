<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Posts;
use App\Buttons\QMButton;
use App\Models\WpPost;
class ListPostsButton extends QMButton {
	public $accessibilityText = 'List Posts';
	public $action = 'datalab/posts';
	public $color = WpPost::COLOR;
	public $fontAwesome = WpPost::FONT_AWESOME;
	public $id = 'datalab-posts-button';
	public $image = WpPost::DEFAULT_IMAGE;
	public $link = 'datalab/posts';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.posts.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\WpPostController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\WpPostController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Posts';
	public $title = 'List Posts';
	public $tooltip = WpPost::CLASS_DESCRIPTION;
	public $visible = true;
}
