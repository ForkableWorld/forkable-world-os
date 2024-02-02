<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\SecretException;
use App\Fields\Field;
use App\Fields\Text;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Properties\Base\BaseRecordSizeInKbProperty;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Types\QMStr;
use App\Utils\SecretHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

trait IsString {
	/**
	 * @return BaseModel[]|Collection
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	protected static function fixTooLong(): array{
		$models = [];
		$qb = static::whereTooLong();
		if(!$qb){
			return [];
		}
		$sql = $qb->toSql();
		$ids = static::pluckIds($qb);
		$max = (new static())->maxLength;
		QMLog::info($ids->count() . " longer than $max characters...");
		foreach($ids as $id){
			$models[] = static::handleTooLong($id);
		}
		return $models;
	}
	/**
	 * @return QMQB
	 */
	protected static function whereTooLong(): ?QMQB{
		$property = new static();
		$maxLength = $property->getMaxLength();
		if(!$maxLength){
			return null;
		}
		$maxLength = $maxLength - 1;
		$qb = static::query();
		$parent = $property->getParentModelOrFirstExample();
		if($parent->hasAttribute(BaseRecordSizeInKbProperty::NAME)){
			$qb->where(BaseRecordSizeInKbProperty::NAME, ">", $maxLength / 1024);
		} else{
			$qb->whereRaw("length($property->name)".' > '.$maxLength);
		}
		return $qb;
	}
	/**
	 * @param int $id
	 * @noinspection PhpUnusedParameterInspection
	 * @return BaseModel
	 */
	protected static function handleTooLong(int $id): BaseModel{
		le("Please implement " . __METHOD__);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getSortableTextField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getSortableTextField($name, $resolveCallback);
	}
	/**
	 * @return string|null
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public function getExample(){
		if($this->example !== null){
			return $this->example;
		}
		if($isPrimary = $this->isPrimary){
			return null;
		} // The database creates this
		$length = 140;
		if($max = $this->getMaxLength()){
			$length = $max - 1;
		}
		if($min = $this->getMinLength()){
			$length = $min + 1;
		}
		return $this->example = Str::random($length);
	}
	public function getMaxLength(): ?int{
		if(!$this->maxLength){
			return null;
		}
		if($this->dbType === QMDB::TYPE_TEXT){
			return $this->maxLength = QMDB::LENGTH_LIMIT_TEXT;
		}
		return $this->maxLength;
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
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Text
	 */
	protected function getSortableTextField(?string $name, $resolveCallback): Text{
		return $this->getTextField($name ?? $this->getTitleAttribute(), $resolveCallback)->sortable(true);
	}
	/**
	 * @throws InvalidStringAttributeException
	 * @throws InvalidAttributeException
	 * @throws InvalidStringException
	 */
	protected function globalValidation(){
		$this->validateString();
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws InvalidStringAttributeException
	 * @throws InvalidStringException
	 */
	protected function validateString(): void{
		$this->validateNotNull();
		// Uncomment for debugging infinite loops $this->logInfo("Validating ".(new \ReflectionClass(static::class))->getShortName()."...");
		$this->validateType();
		$this->validateMaxLength();
		$this->validateMinLength();
		try {
			$this->validateBlackListedStrings();
		} catch (ModelValidationException $e) {
			le("Why are we throwing a ModelValidationException? ", $e);
		}
		$this->validateRequiredStrings();
		$this->validateSecrets();
	}
	protected function validateType(){
		$rawValue = $this->getDBValue();
		if(is_array($rawValue) || is_object($rawValue)){
			$this->throwException("should be a string but got " . QMStr::prettyJsonEncode($this->getDBValue()));
		}
	}
	/**
	 * @return void
	 * @throws InvalidStringAttributeException
	 */
	protected function validateMaxLength(): void{
		$max = $this->maxLength;
		if(!$max){
			return;
		}
		$val = $this->getDBValue();
		if($val === null){
			return;
		}
		if(!is_string($val)){
			$val = json_encode($val);
		}
		$length = strlen($val);
		if($length > $max){
			throw new InvalidStringAttributeException("must contain at most a maximum of $max characters but is $length characters long",
				$val, $this->name, $this->getParentModel());
		}
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws InvalidStringAttributeException
	 */
	protected function validateMinLength(): void{
		$val = $this->getDBValue();
		if(empty($val) && $this->isRequired()){
			$this->throwException("string is required but empty");
		}
		$min = $this->minLength;
		if(!$min){
			return;
		}
		if($val === null){
			return;
		}
		$length = strlen($val);
		if($length < $min){
			throw new InvalidStringAttributeException("must contain at least a minimum of $min characters but is $length characters long",
				$val, $this->name, $this->getParentModel());
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function validateBlackListedStrings(){
		$value = $this->getDBValue();
		if(!$value){
			return;
		}
		if(QMStr::isNullString($value)){
			$this->throwException("strings should not equal null");
		}
		$this->validateShouldNotContain($value);
		$this->validateShouldNotEqual();
	}
	/**
	 * @param string $haystack
	 * @param $blackList
	 * @param bool $ignoreCase
	 * @param string|null $assertionMessage
	 * @throws InvalidAttributeException
	 */
	protected function assertDoesNotContain(string $haystack, $blackList, $ignoreCase = false,
		string $assertionMessage = null){
		try {
			QMStr::assertStringDoesNotContain($haystack, $blackList, $this->name, $ignoreCase,
                $assertionMessage);
		} catch (InvalidStringException $e) {
			$this->throwException(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function validateRequiredStrings(){
		$value = $this->getDBValue();
		if(!$value){
			return;
		}
		if($required = $this->getRequiredStrings()){
			try {
				QMStr::assertStringContains($value, $required, $this->name);
			} catch (InvalidStringException $e) {
				$this->throwException(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	private function validateSecrets(){
		if($this->isPublic){
			try {
				SecretHelper::exceptionIfContainsSecretValue($this->getDBValue(), $this->name);
			} catch (SecretException $e) {
				$this->throwException(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	/**
	 * @param $value
	 * @return void
	 * @throws InvalidAttributeException
	 */
	private function validateShouldNotContain($value): void{
		if($shouldNotContain = $this->getShouldNotContain()){
			if(is_array($value)){
				foreach($value as $one){
					$this->assertDoesNotContain($one, $shouldNotContain);
				}
			} else{
				try {
					QMStr::assertStringDoesNotContain($value, $shouldNotContain, $this->name);
				} catch (InvalidStringException $e) {
					$this->throwException(__METHOD__.": ".$e->getMessage());
				}
			}
		}
	}
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 */
	private function validateShouldNotEqual(): void{
		if($blackListedStrings = $this->getShouldNotEqual()){
			foreach($blackListedStrings as $one){
				$this->assertNotEquals($one, __FUNCTION__);
			}
		}
	}
}
