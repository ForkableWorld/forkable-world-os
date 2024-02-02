<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringAttributeException;
use App\Exceptions\InvalidStringException;
use App\UI\HtmlHelper;
use App\Fields\DateTime;
use App\Fields\Field;
use App\Fields\Text;
trait IsHtml {
	use IsString;
	/**
	 * @return void
	 */
	private function validateHTML(): void{
		if(!$this->isHtml){
			return;
		}
		$val = $this->getDBValue();
		if($val === null){
			return;
		}
		HtmlHelper::validateHtml($val, $this->getName());
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws InvalidStringAttributeException Call this when adding custom validate() functions to traits
	 * @throws InvalidStringException
	 */
	protected function globalValidation(){
		$this->validateNotNull();
		// Uncomment for debugging infinite loops $this->logInfo("Validating ".(new \ReflectionClass(static::class))->getShortName()."...");
		$this->validateType();
		$this->validateString();
		$this->validateHTML();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field|DateTime|Text
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getHtmlField($resolveCallback, $name);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getHtmlField($resolveCallback, $name);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getHtmlField($resolveCallback, $name);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getHtmlField($resolveCallback, $name);
	}
}
