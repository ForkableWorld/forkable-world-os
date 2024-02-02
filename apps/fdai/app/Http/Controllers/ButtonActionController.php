<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\Buttons\RunnableButton;
use App\Slim\View\Request\QMRequest;
use App\Utils\UrlHelper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;
use Response;
class ButtonActionController extends Controller {
	public const BUTTON_ACTION_PATH = 'button-action';
	/**
	 * @param Request $request
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function run(Request $request){
		$className = QMRequest::getParam('class');
		/** @var RunnableButton $button */
		$button = new $className();
		return $button->run($_GET);
	}
	public static function generateUrl(string $class, array $params){
		$params['class'] = $class;
		return UrlHelper::generateApiUrl('button-action', $params);
	}
}
