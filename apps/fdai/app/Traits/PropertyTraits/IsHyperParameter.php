<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringAttributeException;
use App\Exceptions\InvalidStringException;
use App\Utils\AppMode;
trait IsHyperParameter {
	public $isHyperParameter = true;

    /**
     * @return void
     * @throws InvalidAttributeException
     * @throws InvalidStringAttributeException
     * @throws InvalidStringException
     */
    public function validate(): void {
		if(!$this->shouldValidate()){
			return;
		}
		$this->globalValidation();
		$this->validateHyperParameter();
	}
	public function validateHyperParameter(){
		$value = $this->getDBValue();
		if($value === null){
			return;
		}
		$this->assertAPIRequest();
	}
	/** @noinspection PhpUnusedLocalVariableInspection */
	/**
	 * @throws InvalidAttributeException
	 */
	protected function assertAPIRequest(): void{
		if(!AppMode::isApiRequest()){
			$parent = $this->getParentModel();
			if($parent->hasId() && !$parent->wasRecentlyCreated){
				$original = $this->getRawOriginalValue();
				$value = $this->getDBValue();
				if($original === $value || $original === null){
					return;
				}
				if(property_exists($this, 'isCalculated') && $this->isCalculated){
					return;
				}
				$message = "TODO: hyper-parameters should only be modified during API requests but we're 
                changing 
                $parent 
                $this->name 
                from original: " . \App\Logging\QMLog::print_r($original, true) . "
                to new value: " . \App\Logging\QMLog::print_r($value, true);
				if($throw = false){
					$this->throwException($message);
				} else{
					$this->logError($message);
					//$this->setRawAttribute(null);
				}
			}
		}
	}
	public function showOnIndex(): bool{ return false; }
	public function showOnUpdate(): bool{ return true; }
	public function showOnCreate(): bool{ return true; }
	public function showOnDetail(): bool{ return true; }
}
