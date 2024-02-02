<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Buttons\Admin\PHPStormButton;
use App\Storage\DB\Migrations;
use App\Utils\UrlHelper;
use Bestmomo\NiceArtisan\Http\Controllers\NiceArtisanController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function redirect;
class QMArtisanController extends NiceArtisanController {
	const MAKE_MIGRATION = "make:migration";
	const PARAM_STATEMENT = 'statement';
	const PARAM_NAME = 'name';
	public static function getMigrationUrl(string $name, string $statement): string{
		return UrlHelper::getLocalUrl('niceartisan/item/' . self::MAKE_MIGRATION,
			[self::PARAM_NAME => $name, self::PARAM_STATEMENT => $statement]);
	}
	/**
	 * Call the Artisan  command
	 * @param Request $request
	 * @param string $command
	 * @return RedirectResponse
	 */
	public function command(Request $request, $command): RedirectResponse{
		if($command === self::MAKE_MIGRATION){
			$path =
				Migrations::makeMigration($request->input(self::PARAM_NAME), $request->input(self::PARAM_STATEMENT));
			$url = PHPStormButton::redirectUrl($path);
			return redirect($url);
		}
		$response = parent::command($request, $command);
		return $response;
	}
}
