<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\StudyDataTableService;
use App\Http\Controllers\BaseDataLabController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class StudyDataLabController extends BaseDataLabController {
	/**
	 * Display a listing of the StudyController.
	 * @param StudyDataTableService $dataTable
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function index(StudyDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
}
