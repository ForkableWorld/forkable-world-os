<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\Buttons\RunnableButton;
use App\Astral\Actions\DeleteTestUsersAction;
use App\Slim\Middleware\QMAuth;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DeleteTestUsersButton extends RunnableButton {
	public $title = "Delete Test Users";
	public $fontAwesome = FontAwesome::TRASH_ALT;
	public $image = ImageUrls::BASIC_FLAT_ICONS_TRASH;
	public $tooltip = 'Automatically purge database of invalid test data';
	public function __construct(){
		parent::__construct([]);
	}
	public function run(array $parameters = []){
		QMAuth::isAdminOrException();
		DeleteTestUsersAction::deleteOldTestUsers();
		le("Completed " . __METHOD__);
	}
}
