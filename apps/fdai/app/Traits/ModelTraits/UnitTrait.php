<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\Unit\UnitVariableCategoriesWhereDefaultUnitButton;
use App\Buttons\RelationshipButtons\Unit\UnitVariablesWhereDefaultUnitButton;
use App\Files\FileHelper;
use App\Models\Unit;
use App\Slim\Model\QMUnit;
use App\Traits\HardCodable;
use App\Traits\HasButton;
use App\Traits\HasOptions;
use App\Traits\HasSynonyms;
use App\Types\QMStr;
trait UnitTrait {
	use HasSynonyms, HardCodable;
	use HasOptions, HasButton;
	public function getAbbreviatedName(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_ABBREVIATED_NAME] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->abbreviatedName;
		}
	}
	public function setAbbreviatedName(string $abbreviatedName): void{
		$this->setAttribute(Unit::FIELD_ABBREVIATED_NAME, $abbreviatedName);
	}
	public function getUnitCategoryId(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_UNIT_CATEGORY_ID] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->unitCategoryId;
		}
	}
	public function setUnitCategoryId(bool $unitCategoryId): void{
		$this->setAttribute(Unit::FIELD_UNIT_CATEGORY_ID, $unitCategoryId);
	}
	public function getAdvanced(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_ADVANCED] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->advanced;
		}
	}
	public function setAdvanced(bool $advanced): void{
		$this->setAttribute(Unit::FIELD_ADVANCED, $advanced);
	}
	public function getConversionSteps(): ?array{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_CONVERSION_STEPS] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->conversionSteps;
		}
	}
	public function setConversionSteps(array $conversionSteps): void{
		$this->setAttribute(Unit::FIELD_CONVERSION_STEPS, $conversionSteps);
	}
	public function getCreatedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_CREATED_AT] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->createdAt;
		}
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(Unit::FIELD_DELETED_AT, $deletedAt);
	}
	public function getFillingType(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_FILLING_TYPE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->fillingType;
		}
	}
	public function getFillingValueAttribute(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_FILLING_VALUE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->fillingValue;
		}
	}
	public function setFillingValue(float $fillingValue): void{
		$this->setAttribute(Unit::FIELD_FILLING_VALUE, $fillingValue);
	}
	public function getManualTracking(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_MANUAL_TRACKING] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->manualTracking;
		}
	}
	public function setManualTracking(bool $manualTracking): void{
		$this->setAttribute(Unit::FIELD_MANUAL_TRACKING, $manualTracking);
	}
	public function getMaximumDailyValue(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_MAXIMUM_DAILY_VALUE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->maximumDailyValue;
		}
	}
	public function setMaximumDailyValue(float $maximumDailyValue): void{
		$this->setAttribute(Unit::FIELD_MAXIMUM_DAILY_VALUE, $maximumDailyValue);
	}
	public function getMaximumValue(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_MAXIMUM_VALUE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->maximumValue;
		}
	}
	public function setMaximumValue(float $maximumValue): void{
		$this->setAttribute(Unit::FIELD_MAXIMUM_VALUE, $maximumValue);
	}
	public function getMinimumValue(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_MINIMUM_VALUE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->minimumValue;
		}
	}
	public function setMinimumValue(float $minimumValue): void{
		$this->setAttribute(Unit::FIELD_MINIMUM_VALUE, $minimumValue);
	}
	public function setName(string $name): void{
		$this->setAttribute(Unit::FIELD_NAME, $name);
	}
	public function getNumberOfMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->numberOfMeasurements;
		}
	}
	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(Unit::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}
	public function getNumberOfOutcomeCaseStudies(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->numberOfOutcomeCaseStudies;
		}
	}
	public function setNumberOfOutcomeCaseStudies(int $numberOfOutcomeCaseStudies): void{
		$this->setAttribute(Unit::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES, $numberOfOutcomeCaseStudies);
	}
	public function getNumberOfOutcomePopulationStudies(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->numberOfOutcomePopulationStudies;
		}
	}
	public function setNumberOfOutcomePopulationStudies(int $numberOfOutcomePopulationStudies): void{
		$this->setAttribute(Unit::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES, $numberOfOutcomePopulationStudies);
	}
	public function getScale(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_SCALE] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->scale;
		}
	}
	public function setScale(string $scale): void{
		$this->setAttribute(Unit::FIELD_SCALE, $scale);
	}
	public function getUpdatedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Unit::FIELD_UPDATED_AT] ?? null;
		} else{
			/** @var QMUnit $this */
			return $this->updatedAt;
		}
	}
	public function getCompatibleOptions(): array{
		$compatible = $this->getCompatibleUnits();
		return self::toOptions($compatible);
	}
	public static function allOptions(): array{
		return static::toOptions(static::all());
	}
	public static function toOptions(array $units): array{
		$opts = [];
		foreach($units as $unit){
			$opts[$unit->getId()] = $unit->getDisplayNameAttribute();
		}
		return $opts;
	}
	/**
	 * @return string
	 */
	public static function getHardCodedDirectory(): string{
		return FileHelper::absPath("app/Units");
	}
	protected function generateFileContentOfHardCodedModel(): string{
		// TODO: Implement generateFileContentOfHardCodedModel() method.
	}
	protected function getHardCodedShortClassName(): string{
		return QMStr::toShortClassName($this->getNameAttribute()) . "Unit";
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [];
		$arr[] = new UnitVariablesWhereDefaultUnitButton($this);
		$arr[] = new UnitVariableCategoriesWhereDefaultUnitButton($this);
		return $arr;
	}
}
