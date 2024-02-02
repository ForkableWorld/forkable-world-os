<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Votes;
use App\Buttons\QMButton;
use App\Models\Vote;
class ListVotesButton extends QMButton {
	public $accessibilityText = 'List Votes';
	public $action = 'datalab/votes';
	public $color = '#dd4b39';
	public $fontAwesome = Vote::FONT_AWESOME;
	public $id = 'datalab-votes-button';
	public $image = 'https://static.quantimo.do/img/thumbs/thumb_down_black.png';
	public $link = 'datalab/votes';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.votes.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VoteController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VoteController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Votes';
	public $title = 'List Votes';
	public $tooltip = Vote::CLASS_DESCRIPTION;
	public $visible = true;
}
