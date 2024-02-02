<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\InvalidVariableNameException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\PostController;
use App\Slim\QMSlim;
use App\Variables\QMCommonVariable;
class PostVariableController extends PostController {
	public const ERROR_NO_VARIABLES_GIVEN = 'Expected at least one variable, none given';
	public const ERROR_INVALID_COMBINATION_OPERATION = 'Combination operation "%s" is invalid, must be SUM or MEAN';
	/**
	 * @throws CommonVariableNotFoundException
	 * @throws InvalidVariableNameException
	 * @throws UnauthorizedException
	 */
	public function post(){
		$variables = $this->getRequestJsonBodyAsArray(false, true);
		[$numVariables, $existingVariables, $existingVariablesCount] =
			QMCommonVariable::createMultipleVariables($variables);
		if($numVariables === count($existingVariables)){
			$err = 'These variables already exist';
			return $this->writeJsonWithGlobalFields(400, [
				'status' => QMException::CODE_BAD_REQUEST,
				'success' => false,
				'message' => $err,
				'error' => ['message' => $err],
			]);
		} elseif($existingVariablesCount > 0){
			$err = 'These variables already exist: ' . implode(', ', $existingVariables);
			return $this->writeJsonWithGlobalFields(201, [
				'status' => '201',
				'success' => true,
				'message' => $err,
				'error' => ['message' => $err],
			]);
		} else{
			return $this->writeJsonWithGlobalFields(201, [
				'status' => '201',
				'success' => true,
			]);
		}
	}
}
