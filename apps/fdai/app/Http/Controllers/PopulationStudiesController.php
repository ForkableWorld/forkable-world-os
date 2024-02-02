<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Models\Study;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyTypeProperty;
use Illuminate\Http\Request;
class PopulationStudiesController extends StudiesController {
	public function index(Request $request): string{
		if($id = StudyIdProperty::fromRequest()){
			return $this->show($id);
		}
		$request->query->set(Study::FIELD_TYPE, StudyTypeProperty::TYPE_POPULATION);
		$studies = Study::index($request);
		return view('studies-index', ['studies' => $studies]);
	}
}
