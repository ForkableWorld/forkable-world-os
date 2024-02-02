<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\UserDataTableService;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class UserController extends AnalyzableDataLabController {
	public static function getEditProfileUrl(int $id = null): string{
		if(!$id){
			$id = QMAuth::getQMUser()->getUserId();
		}
		return User::generateDataLabEditUrl($id);
	}
	/**
	 * Display a listing of the UserController.
	 * @param UserDataTableService $dataTable
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function index(UserDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
}
