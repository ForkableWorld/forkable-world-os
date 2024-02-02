<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\VariableCategory;
use App\Traits\HasCategories;
use App\Variables\QMVariableCategory;
trait HasVariableCategory {
	use HasCategories;
	abstract public function getVariableCategoryId(): int;
	public function hasVariableCategoryId(): bool{ return true; }
	public function getQMVariableCategory(): QMVariableCategory{
		$nameOrId = $this->getVariableCategoryId();
		return QMVariableCategory::find($nameOrId);
	}
	public function getVariableCategory(): VariableCategory{
		return $this->getQMVariableCategory()->l();
	}
	public function getVariableCategoryName(): string{
		return $this->getQMVariableCategory()->getNameAttribute();
	}
	public function getVariableCategoryButton(): QMButton{
		return $this->getQMVariableCategory()->getButton();
	}
	public function getVariableCategoryNameLink(): string{
		return $this->getQMVariableCategory()->getDataLabDisplayNameLink();
	}
	public function getVariableCategoryLink(array $params = []): string{
		return $this->getQMVariableCategory()->getButton($params)->getImageTextLink();
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getVariableCategorySelector(): string{
		return VariableCategory::getSelector($this->getUnitIdAttribute(), "variable_category_id");
	}
	/**
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function getVariableCategorySelectorOptions(): array{
		return VariableCategory::getSelectorOptions();
	}
	/**
	 * @return QMButton[]
	 */
	public function getCategoryButtons(): array{
		return [$this->getVariableCategory()->getButton()];
	}
}
