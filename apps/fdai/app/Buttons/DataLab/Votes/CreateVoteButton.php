<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Votes;
use App\Buttons\QMButton;
use App\Models\Vote;
class CreateVoteButton extends QMButton {
	public $accessibilityText = 'Create Vote';
	public $action = 'datalab/votes/create';
	public $color = '#dd4b39';
	public $fontAwesome = Vote::FONT_AWESOME;
	public $id = 'datalab-votes-create-button';
	public $image = 'https://static.quantimo.do/img/thumbs/thumb_down_black.png';
	public $link = 'datalab/votes/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.votes.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VoteController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VoteController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Vote';
	public $title = 'Create Vote';
	public $tooltip = Vote::CLASS_DESCRIPTION;
	public $visible = true;
}
