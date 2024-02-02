<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Buttons\Admin\PHPStormButton;
use App\Http\Controllers\Controller;
use App\Solutions\CreateController;
use App\Solutions\CreateException;
use App\Solutions\CreateSolution;
use App\Storage\DB\Migrations;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use function redirect;
class CodeGenerationController extends Controller {
	/**
	 * @param Request $request
	 * @return Application|RedirectResponse|Redirector
	 */
	public function createMigration(Request $request){
		$path = Migrations::makeMigration($request->get('name'), $request->get('statement'));
		return PHPStormButton::redirectToFile($path);
	}
	/**
	 * @param Request $request
	 * @return Application|RedirectResponse|Redirector
	 */
	public function createSolution(Request $request){
		$url = CreateSolution::generate($request->get('name'));
		return redirect($url);
	}
	/**
	 * @param Request $request
	 * @return Application|RedirectResponse|Redirector
	 */
	public function createException(Request $request){
		$url = CreateException::generate($request->get('name'));
		return redirect($url);
	}
	/**
	 * @param Request $request
	 * @return string
	 */
	public function createController(Request $request){
		return CreateController::generate($request->get('name'));
	}
}
