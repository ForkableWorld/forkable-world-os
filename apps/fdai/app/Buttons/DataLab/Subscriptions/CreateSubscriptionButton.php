<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Subscriptions;
use App\Buttons\QMButton;
use App\Models\Subscription;
class CreateSubscriptionButton extends QMButton {
	public $accessibilityText = 'Create Subscription';
	public $action = 'datalab/subscriptions/create';
	public $color = Subscription::COLOR;
	public $fontAwesome = Subscription::FONT_AWESOME;
	public $id = 'datalab-subscriptions-create-button';
	public $image = Subscription::DEFAULT_IMAGE;
	public $link = 'datalab/subscriptions/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.subscriptions.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\SubscriptionController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\SubscriptionController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Subscription';
	public $title = 'Create Subscription';
	public $tooltip = Subscription::CLASS_DESCRIPTION;
	public $visible = true;
}
