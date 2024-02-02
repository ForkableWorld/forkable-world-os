<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Traits\ModelTraits\MeasurementTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MeasurementUserVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Measurement::FIELD_USER_VARIABLE_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_USER_VARIABLE_ID;
	public $ownerKeyName = UserVariable::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variable-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variable';
	public $title = 'User Variable';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;
	/**
	 * MeasurementUserVariableButton constructor.
	 * @param Measurement|MeasurementTrait $methodOrModel
	 * @param BelongsTo|null $relation
	 */
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
		$this->subtitle = $this->relationshipTitle;
		$this->setTextAndTitle($methodOrModel->getVariableName());
	}
}
