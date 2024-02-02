<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Subscriptions;
use App\Buttons\QMButton;
use App\Models\Subscription;
class ListSubscriptionsButton extends QMButton {
	public $accessibilityText = 'List Subscriptions';
	public $action = 'datalab/subscriptions';
	public $color = Subscription::COLOR;
	public $fontAwesome = Subscription::FONT_AWESOME;
	public $id = 'datalab-subscriptions-button';
	public $image = Subscription::DEFAULT_IMAGE;
	public $link = 'datalab/subscriptions';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.subscriptions.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\SubscriptionController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\SubscriptionController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Subscriptions';
	public $title = 'List Subscriptions';
	public $tooltip = Subscription::CLASS_DESCRIPTION;
	public $visible = true;
}
