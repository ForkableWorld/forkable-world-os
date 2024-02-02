<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
abstract class DeleteController extends Controller {
	/**
	 * Initialize the get request and call the abstract function 'deletes' to continue processing.
	 * This function should be called through the Slim framework.
	 */
	final public function initDelete(){
		$this->delete();
	}
	/**
	 * Handle the DELETE request.
	 */
	abstract public function delete();
}
