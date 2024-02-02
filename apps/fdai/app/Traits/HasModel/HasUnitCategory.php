<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\UnitCategory;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasUnitCategory {
	public function getUnitCategoryId(): int{
		$nameOrId = $this->getAttribute('unit_category_id');
		return $nameOrId;
	}
	public function getUnitCategoryButton(): QMButton{
		$unitCategory = $this->getUnitCategory();
		if($unitCategory){
			return $unitCategory->getButton();
		}
		return UnitCategory::generateDataLabShowButton($this->getUnitCategoryId());
	}
	/**
	 * @return UnitCategory
	 */
	public function getUnitCategory(): UnitCategory{
		if($this instanceof BaseProperty && $this->parentModel instanceof UnitCategory){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('unit_category')){
			return $l;
		}
		$id = $this->getUnitCategoryId();
		$unitCategory = UnitCategory::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['unit_category'] = $unitCategory;
		}
		if(property_exists($this, 'unitCategory')){
			$this->unitCategory = $unitCategory;
		}
		return $unitCategory;
	}
	public function getUnitCategoryNameLink(): string{
		return $this->getUnitCategory()->getDataLabDisplayNameLink();
	}
	public function getUnitCategoryImageNameLink(): string{
		return $this->getUnitCategory()->getDataLabImageNameLink();
	}
}
