<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\VariableCategoryDataTableService;
use App\Http\Controllers\BaseDataLabController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class VariableCategoryController extends BaseDataLabController {
	/**
	 * Display a listing of the VariableCategoryController.
	 * @param VariableCategoryDataTableService $dataTable
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function index(VariableCategoryDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
}
