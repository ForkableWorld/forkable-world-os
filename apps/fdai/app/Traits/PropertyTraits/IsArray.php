<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Fields\Field;
use App\Types\QMArr;
trait IsArray {
	use IsJsonEncoded;
	/**
	 * @return array
	 */
	public function getExample(): array{
		if($this->example !== null){
			return $this->example;
		}
		return [];
	}
	/**
	 * @param array|string $value
	 * @return string|null
	 */
	public function toDBValue($value): ?string{
		if(!$value){return "[]";}
		if(is_array($value)){$value = json_encode($value);}
		$lower = $this->isLowerCase();
		if($lower === true){$value = strtolower($value);}
		if($lower === false){$value = strtoupper($value);}
		return $value;
	}
	protected function isLowerCase(): ?bool{
		return null;
	}
	public static function fixJson(string $dbValue): string{
		// Why do we need to removeBackSlashes?    $dbValue = QMStr::removeBackSlashes($dbValue);
		$dbValue = str_replace('""', '"', $dbValue);
		$dbValue = str_replace(']"', ']', $dbValue);
		$dbValue = str_replace(']"', ']', $dbValue);
		$dbValue = str_replace('"[', '[', $dbValue);
		$dbValue = str_replace('"[', '[', $dbValue);
		return $dbValue;
	}
	/**
	 * @return array
	 */
	public function getAccessorValue(): array{
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name);
		if(is_array($val)){
			return $val;
		}
		if(!$val){return [];}
		$fixed = self::fixJson($val);
		if($fixed !== $val){
			$m = $this->getParentModel();
			$this->logError("Fixed $val and setting to $fixed");
			$m->setRawAttribute($this->name, $this->toDBValue($fixed));
			try {
				$m->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return QMArr::toArray($fixed);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function validate(): void {
		$val = $this->getDBValue();
		if($val === null){return;}
		$arr = json_decode($val, true);
		if($arr === null){
			$this->throwException("Could not decode $val because: " . json_last_error_msg());
		}
		if(!is_array($arr)){
			$this->throwException("Could not decode $val to an array. Type is " . gettype($arr));
		}
	}
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return Field
     */
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getSortableTextField($name, $resolveCallback);
    }
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return Field
     */
    public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getSortableTextField($name, $resolveCallback);
    }
    public function getMaxLength(): ?int
    {
        $col = $this->getDBColumn();
        return $this->maxLength = $col->getMaxLength();
    }
}
