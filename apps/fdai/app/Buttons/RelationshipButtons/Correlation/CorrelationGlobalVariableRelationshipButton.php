<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Correlation;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CorrelationGlobalVariableRelationshipButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID;
	public $qualifiedForeignKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID;
	public $ownerKeyName = GlobalVariableRelationship::FIELD_ID;
	public $qualifiedOwnerKeyName = GlobalVariableRelationship::TABLE . '.' . GlobalVariableRelationship::FIELD_ID;
	public $childClass = UserVariableRelationship::class;
	public $parentClass = UserVariableRelationship::class;
	public $qualifiedParentKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ID;
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = 'global_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = FontAwesome::PEOPLE_ARROWS_SOLID;
	public $id = 'global-variable-relationship-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Global Variable Relationship';
	public $title = 'Global Variable Relationship';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
	public function __construct($methodOrModel = null, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
		$this->setTooltip(GlobalVariableRelationship::CLASS_DESCRIPTION);
		$this->title = GlobalVariableRelationship::getClassNameTitle();
	}
	public function getUrl(array $params = []): string{
		return parent::getUrl($params);
	}
	public function getTooltip(): ?string{
		return $this->tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
	}
	public function getTitleAttribute(): string{
		return $this->title = GlobalVariableRelationship::getClassNameTitle();
	}
	public function getMaterialStatCard(): string{
		return HtmlHelper::generateMaterialStatCard("Global Variable Relationship", "The Relationship for the Average Person",
			$this->getTooltip(), $this->getBackgroundColor(), $this->getFontAwesome(), $this->getUrl());
	}
}
