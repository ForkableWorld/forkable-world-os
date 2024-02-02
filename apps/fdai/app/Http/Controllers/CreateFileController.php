<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class CreateFileController extends Controller {
    public function createMigration(Request $request){
        $path = \App\Storage\DB\Migrations::generateMigration(
            $request->get('statement'),
            $request->get('name'));
        return PHPStormController::redirectToFile($path);
    }
    public function createSolution(Request $request){
        $url = \App\Solutions\CreateSolution::generate(
            $request->get('name'));
        return redirect($url);
    }
    public function createException(Request $request){
        $url = \App\Solutions\CreateException::generate(
            $request->get('name'));
        return redirect($url);
    }
    public function createController(Request $request){
        return \App\Solutions\CreateController::generate(
            $request->get('name'));
    }
}
