<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class VariableGlobalVariableRelationshipsWhereCauseVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = 'global_variable_relationships_where_cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'global-variable-relationships-where-cause-variable-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Global Variable Relationships Where Cause Variable';
	public $title = 'Global Variable Relationships Where Cause Variable';
	public $tooltip = 'Global Variable Relationships where this is the Cause Variable';
	public function __construct($methodOrModel, HasMany $relation = null){
		parent::__construct($methodOrModel, $relation);
		$this->setTooltip("Outcomes that could be influenced by " . $this->getVariable()->getTitleAttribute() .
			" based on aggregated population level data");
	}
	/**
	 * @return BaseModel|UserVariable|Model
	 */
	public function getVariable(){
		return $this->getParent();
	}
}
