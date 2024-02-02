<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\CorrelationDataTableService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class CorrelationController extends AnalyzableDataLabController {
	/**
	 * Display a listing of the CorrelationController.
	 * @param CorrelationDataTableService $dataTable
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function index(CorrelationDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
}
