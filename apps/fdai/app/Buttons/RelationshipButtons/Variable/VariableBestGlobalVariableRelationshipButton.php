<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VariableBestGlobalVariableRelationshipButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID;
	public $qualifiedForeignKeyName = Variable::TABLE . '.' . Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID;
	public $ownerKeyName = GlobalVariableRelationship::FIELD_ID;
	public $qualifiedOwnerKeyName = GlobalVariableRelationship::TABLE . '.' . GlobalVariableRelationship::FIELD_ID;
	public $childClass = Variable::class;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = 'best_global_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'best-global-variable-relationship-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Best Global Variable Relationship';
	public $title = 'Best Global Variable Relationship';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
