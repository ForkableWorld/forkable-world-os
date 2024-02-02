<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\DataTableServices\ApplicationDataTableService;
use App\Http\Controllers\BaseDataLabController;
use App\Models\Application;
use App\Utils\UrlHelper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class ApplicationController extends BaseDataLabController {
	/**
	 * Display a listing of the ApplicationController.
	 * @param ApplicationDataTableService $dataTable
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function index(ApplicationDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
	public function show($id){
		$clientId = $this->getClientId($id);
		$url = "https://$clientId.quantimo.do";
		return redirect($url);
	}
	public function edit($id){
		$clientId = $this->getClientId($id);
		$url = UrlHelper::getBuilderUrl($clientId);
		return redirect($url);
	}
	/**
	 * @param $id
	 * @return string
	 */
	public function getClientId($id): string{
		$col = Application::whereId($id)->value(Application::FIELD_CLIENT_ID);
		return $col;
	}
}
