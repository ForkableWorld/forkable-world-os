<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class UserVariableCorrelationsWhereEffectUserVariableButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'correlations_where_effect_user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'user_variable_relationships-where-effect-user-variable-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Predictors';
	public $title = 'Predictors';
	public $tooltip = 'Relationships analyses with other predictor variables whereby this is the assumed outcome in the analysis';
	/**
	 * UserVariableCorrelationsWhereEffectUserVariableButton constructor.
	 * @param $methodOrModel
	 * @param HasMany|null $relation
	 */
	public function __construct($methodOrModel, HasMany $relation = null){
		parent::__construct($methodOrModel, $relation);
		$this->setTooltip("Factors that could influence " . $this->getUserVariable()->getTitleAttribute() .
			" based you YOUR data. ");
	}
	/**
	 * @return BaseModel|UserVariable|Model
	 */
	public function getUserVariable(){
		return $this->getParent();
	}
}
