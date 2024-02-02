<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\ConnectorDataTableService;
use App\Http\Controllers\BaseDataLabController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class ConnectorController extends BaseDataLabController {
	/**
	 * Display a listing of the ConnectorController.
	 * @param ConnectorDataTableService $dataTable
	 * @return \Illuminate\Http\JsonResponse|View
	 */
	public function index(ConnectorDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
}
