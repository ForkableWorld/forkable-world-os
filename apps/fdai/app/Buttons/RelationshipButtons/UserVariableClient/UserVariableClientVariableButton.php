<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariableClient;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\UserVariableClient;
class UserVariableClientVariableButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = UserVariableClient::FIELD_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE.'.'.Variable::FIELD_ID;
	public $childClass = UserVariableClient::class;
	public $parentClass = UserVariableClient::class;
	public $qualifiedParentKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Variable';
	public $title = 'Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;

}
