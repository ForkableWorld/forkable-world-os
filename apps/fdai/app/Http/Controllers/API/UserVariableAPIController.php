<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\BaseJsonResource;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Middleware\QMAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
/** Class UserVariableController
 * @package App\Http\Controllers\API
 */
class UserVariableAPIController extends BaseAPIController {
    public function find($id): ?BaseModel
    {
        if(is_string($id) && !is_numeric($id)){
            $name = urldecode($id);
            $v = Variable::findByNameOrId($name);
            if($v){
                $uv = $v->getUserVariable(QMAuth::getUserId());
            }
        } else {
            $uv = UserVariable::find($id);
        }
        if(isset($uv)){
            $uv->getOrSetHighchartConfigs();
        }
        return $uv ?? null;
    }
	/**
	 * @param $id
	 * @return UserVariable
	 */
	protected function findModel($id){
		if(!is_numeric($id)){
			$name = urldecode($id);
			$v = Variable::findByNameOrId($name);
			if($v){
				$uv = $v->getUserVariable(QMAuth::getUserId());
			}
		} else {
			$uv = UserVariable::find($id);
		}
		if(!isset($uv)){
			throw new ModelNotFoundException("UserVariable not found: $id");
		}
		$uv->getOrSetHighchartConfigs();
		return $uv;
	}
    /**
     * @param \Illuminate\Http\Request $request
     * @return BaseJsonResource
     */
    public function index(\Illuminate\Http\Request $request): BaseJsonResource
    {
       $models = [];
        $name = VariableNameProperty::fromRequest(false);
        if($name){
            $v = Variable::findByRequest();
            if($v){
                $uv = $v->getUserVariable(QMAuth::getUserId());
                if($uv){
                    $uv->getOrSetHighchartConfigs();
                }
                $models = [$uv];
            }
        } else {
            $models = UserVariable::index($request);
        }
        return $this->respondWithJsonResourceCollection($models);
    }

}
