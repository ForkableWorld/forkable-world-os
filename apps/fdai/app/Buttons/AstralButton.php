<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\BaseModel;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
use DigitalCreative\CollapsibleResourceManager\Resources\NovaResource;

abstract class AstralButton extends QMButton {
	protected $class;
	/**
	 * @return BaseModel
	 */
	public function getClass(): string{
		return $this->class;
	}
	/**
	 * @return BaseModel
	 */
	public function getAstralResourceClass(): string{
		$class = $this->getClass();
		return $class::getAstralResourceClass();
	}
	public function getAstralMenuItem(): AbstractResource{
		return NovaResource::make($this->getAstralResourceClass())->icon($this->getFontAwesomeHtml())
			->label($this->getTitleAttribute());
	}
}
