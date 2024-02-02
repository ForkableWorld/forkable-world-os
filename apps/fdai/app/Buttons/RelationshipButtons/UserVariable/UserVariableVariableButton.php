<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariable;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserVariableVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserVariable::FIELD_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = UserVariable::class;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = "Aggregated Variable";
	public $title = "Aggregated Variable";
	public $tooltip = Variable::CLASS_DESCRIPTION;
	/**
	 * UserVariableVariableButton constructor.
	 * @param $methodOrModel
	 * @param BelongsTo|null $relation
	 */
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
