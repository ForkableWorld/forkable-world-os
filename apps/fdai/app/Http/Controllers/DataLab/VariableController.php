<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\DataLab;
use App\Buttons\Admin\PHPStormButton;
use App\DataTableServices\VariableDataTableService;
use App\Variables\QMCommonVariable;
class VariableController extends AnalyzableDataLabController {
	/** @noinspection PhpMissingReturnTypeInspection */
	public function index(VariableDataTableService $dataTable){
		return $dataTable->render(qm_request()->getViewPathByType('index'));
	}
	public function create(){
		return parent::create();
	}
	public function store(){
		return parent::store();
	}
	public function show($id){
		return parent::show($id);
	}
	public function edit($id){
		return parent::edit($id);
	}
	public function destroy($id){
		return parent::destroy($id);
	}
	public function generateCode($id){
		$v = QMCommonVariable::find($id);
		$path = $v->generateChildModelCode();
		return redirect(PHPStormButton::redirectUrl($path));
	}
}
