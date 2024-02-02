<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class BugsnagButton extends AdminButton {
	public $fontAwesome = FontAwesome::BUG_SOLID;
	public $image = ImageUrls::PROFESSIONS_AND_JOBS_CIRCLES_PROGRAMMER;
	public $title = "Bugsnag";
	public $link = 'https://app.bugsnag.com/quantimodo/slim-api/errors';
	public $subtitle = "PHP Errors";
	public function __construct(){
//		$url = 'https://app.bugsnag.com/quantimodo/slim-api/errors?filters[event.since][0]=' .
//			urlencode(date('c', time() - 60)) . '.000Z&filters[error.status][0]=open&filters[event.before][0]=' .
//			urlencode(date('c', time() + 60)) . '.000Z';
		parent::__construct();
	}
}
