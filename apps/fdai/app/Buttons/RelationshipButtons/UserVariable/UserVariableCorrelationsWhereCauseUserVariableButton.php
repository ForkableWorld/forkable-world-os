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
class UserVariableCorrelationsWhereCauseUserVariableButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'correlations_where_cause_user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME_EFFECTS;
	public $id = 'user_variable_relationships-where-cause-user-variable-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Effects';
	public $title = 'Effects';
	public $tooltip = 'Relationships with other outcome variables whereby this is the assumed predictor in the analysis';
	/**
	 * UserVariableCorrelationsWhereCauseUserVariableButton constructor.
	 * @param $methodOrModel
	 * @param HasMany|null $relation
	 */
	public function __construct($methodOrModel, HasMany $relation = null){
		parent::__construct($methodOrModel, $relation);
		$this->setTooltip("Outcomes that could be influenced by " . $this->getUserVariable()->getTitleAttribute() .
			" based you YOUR data. ");
	}
	/**
	 * @return BaseModel|UserVariable|Model
	 */
	public function getUserVariable(){
		return $this->getParent();
	}
}
