<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Posts;
use App\Buttons\QMButton;
use App\Models\WpPost;
class CreatePostButton extends QMButton {
	public $accessibilityText = 'Create Post';
	public $action = 'datalab/posts/create';
	public $color = WpPost::COLOR;
	public $fontAwesome = WpPost::FONT_AWESOME;
	public $id = 'datalab-posts-create-button';
	public $image = WpPost::DEFAULT_IMAGE;
	public $link = 'datalab/posts/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.posts.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\WpPostController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\WpPostController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Post';
	public $title = 'Create Post';
	public $tooltip = WpPost::CLASS_DESCRIPTION;
	public $visible = true;
}
