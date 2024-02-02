<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\DataSources\QMDataSource;
use App\Http\Controllers\BaseAPIController;
use App\Models\Connector;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use Illuminate\Http\Request;
/** Class ConnectorController
 * @package App\Http\Controllers\API
 */
class ConnectorAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Database\Eloquent\Collection|array $models
	 * @return string
	 */
	public function jsonEncodeByName(\Illuminate\Database\Eloquent\Collection|array $models): string{
		$byName = [];
		$models = QMDataSource::all();
		foreach($models as $model){
			$arr = $model->toArray();
			$arr = ObjectHelper::unsetNullAndEmptyArrayOrStringProperties($arr);
			foreach($arr as $key => $val){
				if(str_starts_with($key, 'number_of')){continue;}
				if(in_array($key, [
					'clientRequiresSecret',
					'affiliate',
					'connected',
					'mobileConnectMethod',
					'premium',
					'buttons',
					'qm_client',
					'is_public',
					'client_id',
					'created_at',
					'updated_at'
				])){continue;}
				$byName[$model->name][$key] = $val;
			}
		}
		$str = QMStr::prettyJsonEncode($byName, null, false);
		return $str;
	}
	public function index(Request $request){
		$models = Connector::index($request);
		if($request->get('byName')){
			return $this->jsonEncodeByName($models);
		}
		return $this->respondWithJsonResourceCollection($models);
	}
}
