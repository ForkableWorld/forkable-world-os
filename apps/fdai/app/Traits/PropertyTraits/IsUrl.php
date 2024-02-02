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
trait IsUrl {
	use IsString;
	protected function globalValidation(){
		$this->validateURL();
	}
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 * @throws InvalidStringAttributeException
	 * @throws InvalidStringException
	 */
	private function validateURL(): void{
		$this->validateString();
		if($this->isUrl || $this->isImageUrl){
			$val = $this->getDBValue();
			if($val === null){
				return;
			}
			HtmlHelper::validateHtml($val, $this->getName());
		}
	}
}
