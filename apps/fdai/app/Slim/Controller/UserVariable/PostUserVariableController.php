<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserVariable;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Variable\VariableUpcOneFourProperty;
use App\Slim\Controller\PostController;
use App\Slim\View\Request\QMRequest;
/** Variables
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/v1/userVariables",
 *     description="User specified parameters for variable display and analysis",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="UserVariables",
 *     @SWG\Property(
 *         name="user", type="int", required=true, description="User ID",
 *         name="variable", type="string", required=true, description="Variable DISPLAY name",
 *         name="durationOfAction", type="int", required=true, description="Estimated duration of time following the
 *     onset delay in which a stimulus produces a perceivable effect", name="fillingValue", type="int", required=true,
 *     description="fillingValue", name="joinWith", type="string", required=true, description="joinWith",
 *     name="maximumValue", type="float", required=true, description="maximumValue", name="minimumValue", type="float",
 *     required=true, description="minimumValue", name="name", type="string", required=true, description="name",
 *     name="onsetDelay", type="int", required=true, description="onsetDelay", name="unit", type="string",
 *     required=true, description="unit"
 *     ),
 * )
 */
class PostUserVariableController extends PostController {
	/**
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws ModelValidationException
	 */
	public function post(){
		$userVariables = [];
		$arr = QMRequest::body();
		$clientId = $arr['clientId'] ?? BaseClientIdProperty::fromRequest();
		unset($arr['clientId']);
		unset($arr['userTagVariables']);
		if(!isset($arr[0])){$arr = [$arr];}
		foreach($arr as $data){
			if($clientId){
				$data[UserVariable::FIELD_CLIENT_ID] = $clientId;
			}
			if(!empty($data['upc'])){
				VariableUpcOneFourProperty::updateFromData($arr, Variable::findByData($data));
				unset($data['upc']);
			}
			$userVariables[] = UserVariable::upsertOne($data);
		}
		$slim = UserVariable::toDBModels($userVariables);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
			'userVariables' => $slim,
			'userVariable' => $slim[0] // Backward compatibility
		]);
	}
}
