<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\View\Request\QMRequest;
use App\Variables\QMCommonVariable;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
/** Class PostController
 * @package App\Slim\Controller
 */
abstract class PostController extends Controller {
	/**
	 * Initialize the get request and call the abstract function 'post' to continue processing.
	 * This function should be called through the Slim framework.
	 * @noinspection PhpUnused
	 */
	final public function initPost(){
		if(!isset($_SERVER['REQUEST_METHOD'])){
			$_SERVER['REQUEST_METHOD'] = QMRequest::METHOD_POST;
		} // For tests
		$this->post();
	}
	/**
	 * Handle the POST request.
	 */
	abstract public function post();
	/**
	 * @param bool $throwException
	 * @return string
	 */
	public function getCauseVariableName(bool $throwException = false): ?string{
		$requestBody =
            static::getBodyAsArrayAndReplaceLegacyKeys(false, QMUserVariableRelationship::getLegacyRequestParameters());
		if(isset($requestBody['causeVariableName'])){
			return $requestBody['causeVariableName'];
		}
		if(isset($requestBody['cause'])){
			return $requestBody['cause'];
		}
		if(isset($requestBody['causeVariableId'])){
			$commonVariable = QMCommonVariable::find($requestBody['causeVariableId']);
			if($commonVariable){
				return $commonVariable->name;
			}
		}
		if($throwException){
			throw new BadRequestHttpException("Please provide causeVariableName or causeVariableId in body");
		}
		return null;
	}
	/**
	 * @param bool $throwException
	 * @return string
	 */
	public function getEffectVariableName(bool $throwException = false): ?string{
		$requestBody =
            static::getBodyAsArrayAndReplaceLegacyKeys(false, QMUserVariableRelationship::getLegacyRequestParameters());
		if(isset($requestBody['effectVariableName'])){
			return $requestBody['effectVariableName'];
		}
		if(isset($requestBody['effect'])){
			return $requestBody['effect'];
		}
		if(isset($requestBody['effectVariableId'])){
			$commonVariable = QMCommonVariable::find($requestBody['effectVariableId']);
			if($commonVariable){
				return $commonVariable->name;
			}
		}
		if($throwException){
			throw new BadRequestHttpException("Please provide effectVariableName or effectVariableId in body");
		}
		return null;
	}
	/**
	 * @param bool $throwException
	 * @return int
	 */
	public function getCauseVariableId(bool $throwException = false): ?int{
		$requestBody =
            static::getBodyAsArrayAndReplaceLegacyKeys(false, QMUserVariableRelationship::getLegacyRequestParameters());
		if(isset($requestBody['causeVariableId'])){
			return (int)$requestBody['causeVariableId'];
		}
		if($this->getCauseVariableName()){
			$commonVariable = QMCommonVariable::find($this->getCauseVariableName());
			if($commonVariable){
				return $commonVariable->getVariableIdAttribute();
			}
		}
		if($throwException){
			throw new BadRequestHttpException("Please provide causeVariableName or causeVariableId in body");
		}
		return null;
	}
	/**
	 * @param bool $throwException
	 * @return int
	 */
	public function getEffectVariableId(bool $throwException = false): ?int{
		$requestBody =
            static::getBodyAsArrayAndReplaceLegacyKeys(false, QMUserVariableRelationship::getLegacyRequestParameters());
		if(isset($requestBody['effectVariableId'])){
			return (int)$requestBody['effectVariableId'];
		}
		if($this->getEffectVariableName()){
			$commonVariable = QMCommonVariable::find($this->getEffectVariableName());
			if($commonVariable){
				return $commonVariable->getVariableIdAttribute();
			}
		}
		if($throwException){
			throw new BadRequestHttpException("Please provide effectVariableName or effectVariableId in body");
		}
		return null;
	}

	/**
	 * @param $data
	 */
	public function write($data){
		$this->getApp()->writeJsonWithGlobalFields(201, $data);
	}
}
