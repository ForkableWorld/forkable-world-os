<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Correlation;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\UI\HtmlHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CorrelationCauseUserVariableButton extends BelongsToRelationshipButton {
	const TOOLTIP = "Click for more details about the predictor variable. ";
	public $interesting = true;
	public $foreignKeyName = UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID;
	public $ownerKeyName = UserVariable::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $childClass = UserVariableRelationship::class;
	public $parentClass = UserVariableRelationship::class;
	public $qualifiedParentKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'cause_user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'cause-user-variable-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'Cause User Variable';
	public $title = 'Cause User Variable';
	public $tooltip = self::TOOLTIP;
	/**
	 * CorrelationCauseUserVariableButton constructor.
	 * @param UserVariableRelationship|string $methodOrModel
	 * @param BelongsTo|null $relation
	 */
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
		$c = $this->getParent();
		$v = $c->getCauseVariable();
		// We have to do this special because we link to user variable but use variable info for button
		$this->populateByModel($v);
		$this->setBadgeText("Predictor");
		$this->setTooltip((new BaseCauseVariableIdProperty())->description);
	}
	public function getMaterialStatCard(): string{
		$c = $this->getParent();
		$v = $c->getCauseVariable();
		return HtmlHelper::generateMaterialStatCard($v->getDisplayNameAttribute(), "Predictor User Variable",
			$this->getTooltip(), $this->getBackgroundColor(), $this->getFontAwesome(), $this->getUrl());
	}
	/**
	 * @return UserVariableRelationship
	 */
	public function getParent(): ?BaseModel{
		return parent::getParent();
	}
	public function getTooltip(): ?string{
		return $this->tooltip = self::TOOLTIP;
	}
}
