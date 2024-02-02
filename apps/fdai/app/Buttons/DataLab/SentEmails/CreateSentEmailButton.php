<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\SentEmails;
use App\Buttons\QMButton;
use App\Models\SentEmail;
class CreateSentEmailButton extends QMButton {
	public $accessibilityText = 'Create Sent Email';
	public $action = 'datalab/sentEmails/create';
	public $color = SentEmail::COLOR;
	public $fontAwesome = SentEmail::FONT_AWESOME;
	public $id = 'datalab-sent-emails-create-button';
	public $image = SentEmail::DEFAULT_IMAGE;
	public $link = 'datalab/sentEmails/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.sentEmails.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\SentEmailController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\SentEmailController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Sent Email';
	public $title = 'Create Sent Email';
	public $tooltip = SentEmail::CLASS_DESCRIPTION;
	public $visible = true;
}
