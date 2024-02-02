<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\Unit;
use App\Slim\Model\QMUnit;
use App\UnitCategories\RatingUnitCategory;
trait HasUnit {
	public function getQMUnit(): QMUnit{
		if($exists = property_exists($this, 'unit')){
			$unit = $this->unit;
			if($unit instanceof QMUnit){
				return $unit;
			}
		}
		$id = $this->getUnitIdAttribute();
		if(!$id){
			le("No Unit id!");
		}
		return QMUnit::getByNameOrId($id);
	}
	/**
	 * @return Unit
	 */
	public function getUnit(): Unit{
		if(property_exists($this, 'unit') && $this->unit instanceof Unit){
			return $this->unit;
		}
		$unit = $this->getQMUnit();
		return $unit->l();
	}
	public function getUnitButton(): QMButton{
		return $this->getQMUnit()->getButton();
	}
	public function getUnitLink(): string{
		$id = $this->getUnitIdAttribute();
		if(!$id){
			return "N/A";
		}
		return $this->getQMUnit()->getDataLabDisplayNameLink();
	}
	abstract public function getUnitIdAttribute(): ?int;
	public function getUnitAbbreviatedLink(array $params = []): string{
		$url = $this->getUrl($params);
		$abbreviatedName = $this->getQMUnit()->abbreviatedName;
		$displayName = $this->getQMUnit()->getTitleAttribute();
		return "<a href=\"$url\" target='_blank' title=\"See $displayName Details\">$abbreviatedName</a>";
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getUnitSelector(): string{
		return Unit::getSelector($this->getUnitIdAttribute(), "unit_id");
	}
	/**
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function getUnitSelectorOptions(): array{
		return Unit::getSelectorOptions();
	}
	public function isRating(): bool{
		$QMUnit = $this->getQMUnit();
		return $QMUnit->unitCategoryId === RatingUnitCategory::ID;
	}
	public function getUnitAbbreviatedName(): string{
		return $this->getQMUnit()->abbreviatedName;
	}
	public function getCompatibleUnitOptions(): array{
		if($this->getUnitIdAttribute()){
			return $this->getQMUnit()->getCompatibleOptions();
		} else{
			return QMUnit::allOptions();
		}
	}
}
