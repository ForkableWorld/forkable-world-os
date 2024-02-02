<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Buttons\Admin\PHPStormButton;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
class PHPStormController extends Controller {
	/**
	 * @return RedirectResponse
	 */
	public function get(){
		return PHPStormButton::redirectToFile();
	}
}
