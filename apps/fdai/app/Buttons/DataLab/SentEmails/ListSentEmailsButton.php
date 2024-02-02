<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\SentEmails;
use App\Buttons\QMButton;
use App\Models\SentEmail;
class ListSentEmailsButton extends QMButton {
	public $accessibilityText = 'List Sent Emails';
	public $action = 'datalab/sentEmails';
	public $color = SentEmail::COLOR;
	public $fontAwesome = SentEmail::FONT_AWESOME;
	public $id = 'datalab-sent-emails-button';
	public $image = SentEmail::DEFAULT_IMAGE;
	public $link = 'datalab/sentEmails';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.sentEmails.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\SentEmailController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\SentEmailController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Sent Emails';
	public $title = 'List Sent Emails';
	public $tooltip = SentEmail::CLASS_DESCRIPTION;
	public $visible = true;
}
