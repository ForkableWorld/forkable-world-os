<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\RelationshipButtons\CommonTag\CommonTagTaggedVariableButton;
use App\Buttons\RelationshipButtons\CommonTag\CommonTagTagVariableButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Models\CommonTag;
use App\Variables\QMCommonTag;
trait CommonTagTrait {
	public function getConversionFactor(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_CONVERSION_FACTOR] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->conversionFactor;
		}
	}
	public function setConversionFactor(float $conversionFactor): void{
		$this->setAttribute(CommonTag::FIELD_CONVERSION_FACTOR, $conversionFactor);
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(CommonTag::FIELD_DELETED_AT, $deletedAt);
	}
	public function getNumberOfDataPoints(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_NUMBER_OF_DATA_POINTS] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->numberOfDataPoints;
		}
	}
	public function setNumberOfDataPoints(int $numberOfDataPoints): void{
		$this->setAttribute(CommonTag::FIELD_NUMBER_OF_DATA_POINTS, $numberOfDataPoints);
	}
	public function getStandardError(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_STANDARD_ERROR] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->standardError;
		}
	}
	public function setStandardError(float $standardError): void{
		$this->setAttribute(CommonTag::FIELD_STANDARD_ERROR, $standardError);
	}
	public function getTagVariableId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_TAG_VARIABLE_ID] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->tagVariableId;
		}
	}
	public function setTagVariableId(int $tagVariableId): void{
		$this->setAttribute(CommonTag::FIELD_TAG_VARIABLE_ID, $tagVariableId);
	}
	public function getTagVariableUnitId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_TAG_VARIABLE_UNIT_ID] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->tagVariableUnitId;
		}
	}
	public function setTagVariableUnitId(int $tagVariableUnitId): void{
		$this->setAttribute(CommonTag::FIELD_TAG_VARIABLE_UNIT_ID, $tagVariableUnitId);
	}
	public function getTaggedVariableId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_TAGGED_VARIABLE_ID] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->taggedVariableId;
		}
	}
	public function setTaggedVariableId(int $taggedVariableId): void{
		$this->setAttribute(CommonTag::FIELD_TAGGED_VARIABLE_ID, $taggedVariableId);
	}
	public function getTaggedVariableUnitId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID] ?? null;
		} else{
			/** @var QMCommonTag $this */
			return $this->taggedVariableUnitId;
		}
	}
	public function setTaggedVariableUnitId(int $taggedVariableUnitId): void{
		$this->setAttribute(CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID, $taggedVariableUnitId);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new CommonTagTaggedVariableButton($this),
			new CommonTagTagVariableButton($this),
		];
	}
}
