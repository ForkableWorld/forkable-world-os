<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\RedundantVariableParameterException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Utils\AppMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;
use LogicException;
use Tests\QMBaseTestCase;
use Throwable;
use Watson\Validating\ValidatingTrait;
use Watson\Validating\ValidationException;

trait QMValidatingTrait {
	use ValidatingTrait;
	use HasPropertyModels;
	protected array $rules = [];
	// validation. If not set, it will default to false.
	/**
	 * @return void
	 * @throws ModelValidationException
	 * @throws ValidationException
	 */
	public function fixInvalidRecords(){
		$rulesForAllFields = $this->getRules();
		foreach($rulesForAllFields as $field => $ruleStringForField){
			$rules = explode("|", $ruleStringForField);
			$numeric = $this->fieldIsNumeric($field);
			foreach($rules as $rule){
				if($numeric){
					if(strpos($rule, "max:") !== false){
						$max = (float)str_replace("max:", "", $rule);
						/** @var Builder $qb */
						$qb = static::where($field, ">", $max);
						$arr = $qb->get();
						foreach($arr as $item){
							/** @var BaseModel $item */
							$item->$field = null;
							$item->isValidOrFail();
							$item->save();
						}
					}
					if(strpos($rule, "min:") !== false){
						$min = (float)str_replace("min:", "", $rule);
					}
				}
			}
		}
	}
	/**
	 * @param string $fieldName
	 * @return string
	 */
	protected function getFieldCast(string $fieldName): string{
		$casts = $this->getCasts();
		return $casts[$fieldName];
	}
	/**
	 * @param string $fieldName
	 * @return bool
	 */
	protected function fieldIsNumeric(string $fieldName): bool{
		$cast = static::getFieldCast($fieldName);
		return $cast === "integer" || $cast === "float";
	}

    /**
	 * Throw a validation exception.
	 * @throws ModelValidationException
	 */
	public function throwValidationException(){
		throw new ModelValidationException($this->getModel());
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 */
	public function validate(): void{
		$required = $this->getRequiredFields();
		foreach($required as $col){
			if(!isset($this->attributes[$col])){
				throw new InvalidAttributeException($this, $col, null, "is required");
			}
		}
		$properties = $this->getModifiedPropertyModels();
		$valid = true;
		$bag = new MessageBag();
		foreach($properties as $property){
			if(!$property){
                le('!$property');
            }
			try {
                try {
                    $property->validate();
                } catch (RedundantVariableParameterException $e) {
                    $property->setValue(null);
                }
            } catch (InvalidAttributeException $e) {
				$valid = false;
				$isTest = AppMode::isAnyKindOfUnitTest();
				$expected = ExceptionHandler::getExpectedRequestException();
				if($isTest && $expected !== ModelValidationException::class){
					try {
                        $property->validate();
					} catch (InvalidAttributeException|RedundantVariableParameterException $e) {
                        le($e);
                    }
                }
				$bag->add($property->name, $e->getMessage());
			}
		}
		$this->setErrors($bag);
		if(!$valid){
			$this->throwValidationException();
		}
	}
	/**
	 * Validate the model against it's rules, returning whether
	 * or not it passes and setting the error messages on the
	 * model if required.
	 * @param array $rules
	 * @return bool
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function performValidation($rules = []){
		try {
			$this->validate();
			return true;
		} catch (ModelValidationException $e) {
			$message1 = $e->getMessage();
			if(empty($message1)){$message1 = "No message from ModelValidationException";}
			$this->logError(__METHOD__.": ".$message1);
			$this->setErrors($e->getMessageBag());
			return false;
		} catch (Throwable $e) {
			$message1 = $e->getMessage();
			if(empty($message1)){$message1 = "No message from Throwable";}
			debugger(__METHOD__.": Could not validate because: ".$message1);
			$this->validate();
			return true;
		}
	}
	/**
	 * Perform a one-off save that will raise an exception on validation error
	 * instead of returning a boolean (which is the default behaviour).
	 * @param array $options
	 * @return bool
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function saveOrFail(array $options = []){
		$this->validate();
		try {
			if(AppMode::isUnitOrStagingUnitTest()){
				$c = $this->getConnection();
				$c->enableQueryLog();
			}
			return parent::saveOrFail($options);
		} catch (Throwable $e) {
			// Put a break point at Illuminate/Database/Connection.php:483 and uncomment below to debug SQL errors
			//return parent::saveOrFail($options);
			if(stripos($e->getMessage(), "column 'variables.charts'") !== false){
				QMLog::error("charts => " . json_encode($this->charts, JSON_PRETTY_PRINT));
				/** @var LogicException $e */
				throw $e;
			}
			le($e->getMessage(), QMLog::truncate(\App\Logging\QMLog::print_r($this->attributesToArray(), true)));
		}
	}
	/**
	 * Get the model.
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this;
	}

    /**
	 * @param string $key
	 * @param $value
	 * @throws InvalidAttributeException
	 */
	public function validateAndSet(string $key, $value){
		$this->setAttribute($key, $value);
		$this->validateAttribute($key);
	}
	/**
	 * @param string $attribute
	 * @throws InvalidAttributeException
	 */
	public function validateAttribute(string $attribute){
		$value = $this->getAttributeValue($attribute);
		if($propertyModel = $this->getPropertyModel($attribute)){
			$propertyModel->validate();
		} else{
			if(isset($this->rules[$attribute])){
				/** @var Validator $v */
				$v = \Illuminate\Support\Facades\Validator::getFacadeRoot()
					->make([$attribute => $value], [$attribute => $this->rules[$attribute]]);
				if(!$v->passes()){
					$this->setErrors($v->errors());
					throw new InvalidAttributeException($this, $attribute, $value, $v->errors());
				}
			}
		}
	}

    /**
	 * @param string $attributeName
	 * @param $attributeValue
	 * @param string $message
	 */
	public function addValidationError(string $attributeName, $attributeValue, string $message){
		$e = new InvalidAttributeException($this, $attributeName, $attributeValue, $message);
		$bag = $this->getErrors();
		$bag->add($attributeName, $e->getValidationMessage());
		$this->setErrors($bag);
	}
	/**
	 * @param string $attributeName
	 * @param $attributeValue
	 * @param string $message
	 * @throws ModelValidationException
	 */
	public function throwModelValidationException(string $attributeName, $attributeValue, string $message){
		$this->addValidationError($attributeName, $attributeValue, $message);
		throw new ModelValidationException($this);
	}
}
