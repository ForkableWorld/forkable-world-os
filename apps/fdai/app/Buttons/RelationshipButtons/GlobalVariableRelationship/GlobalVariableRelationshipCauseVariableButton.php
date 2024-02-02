<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\GlobalVariableRelationship;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\GlobalVariableRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class GlobalVariableRelationshipCauseVariableButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID;
	public $qualifiedForeignKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE.'.'.Variable::FIELD_ID;
	public $childClass = GlobalVariableRelationship::class;
	public $parentClass = GlobalVariableRelationship::class;
	public $qualifiedParentKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'cause-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Cause Variable';
	public $title = 'Cause Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
    /**
     * CorrelationCauseUserVariableButton constructor.
     * @param GlobalVariableRelationship $methodOrModel
     * @param BelongsTo|null $relation
     */
    public function __construct($methodOrModel, BelongsTo $relation = null){
        parent::__construct($methodOrModel, $relation);
        $this->setTextAndTitle($methodOrModel->getCauseVariableName());
    }
}
