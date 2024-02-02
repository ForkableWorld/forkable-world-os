<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\TrackingReminderDataTableService;
use App\Http\Controllers\BaseDataLabController;
class TrackingReminderController extends BaseDataLabController {
	/**
	 * @param \App\DataTableServices\TrackingReminderDataTableService $dataTable
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
	 */
	public function index(TrackingReminderDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Response|\View
	 */
	public function create(){
		return parent::create();
	}
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Response|\View
	 */
	public function store(){
		return parent::store();
	}
	/**
	 * @param int $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Response|string|\View
	 */
	public function show($id){
		return parent::show($id);
	}
	/**
	 * @param int $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View|\Response
	 */
	public function edit($id){
		return parent::edit($id);
	}
	/**
	 * @param int $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Response|\View
	 * @throws \Exception
	 */
	public function destroy($id){
		return parent::destroy($id);
	}
}
