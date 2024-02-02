<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Model;
use App\Actions\HardDeleteQueryAction;
use App\Buttons\RunnableButton;
use App\Slim\View\Request\QMRequest;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DeleteAllQueryButton extends RunnableButton {
	public $image = ImageUrls::BASIC_FLAT_ICONS_TRASH;
	public $fontAwesome = FontAwesome::TRASH_ALT;
	public $title = "Delete All Records";
	public function __construct(){
		$params = $_GET;
		if(!$params){
			le("No params for DeleteAllQueryButton action!");
		}
		parent::__construct($_GET);
	}
	public function run(array $parameters = []){
		$action = new HardDeleteQueryAction(QMRequest::getTableName(), QMRequest::getQueryParams(),
			"user pressed " . static::class);
		return $action->run();
	}
}
