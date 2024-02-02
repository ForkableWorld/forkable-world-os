<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\GlobalVariableRelationship;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Variable;
use App\Models\GlobalVariableRelationship;
class GlobalVariableRelationshipVariablesWhereBestGlobalVariableRelationshipButton extends HasManyRelationshipButton {
    public $interesting = true;
	public $parentClass = GlobalVariableRelationship::class;
	public $qualifiedParentKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variables_where_best_global_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variables-where-best-global-variable-relationship-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Variables Where Best Global Variable Relationship';
	public $title = 'Variables Where Best Global Variable Relationship';
	public $tooltip = 'Variables where this is the Best Global Variable Relationship';

}
