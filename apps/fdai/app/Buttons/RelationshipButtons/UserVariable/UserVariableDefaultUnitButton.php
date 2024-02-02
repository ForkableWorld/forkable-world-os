<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Slim\Model\QMUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserVariableDefaultUnitButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserVariable::FIELD_DEFAULT_UNIT_ID;
	public $qualifiedForeignKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_DEFAULT_UNIT_ID;
	public $ownerKeyName = Unit::FIELD_ID;
	public $qualifiedOwnerKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $childClass = UserVariable::class;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = 'default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'default-unit-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Unit';
	public $title = 'Unit';
	public $tooltip = Unit::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
	/**
	 * @return UserVariable|Model
	 */
	public function getUserVariable(): UserVariable{
		return $this->getButtonRelation()->getChild();
	}
	public function getUnit(): QMUnit{
		return $this->getUserVariable()->getQMUnit();
	}
	public function getRelated(){
		return $this->getUnit();
	}
	public function getRelatedIds(): array{
		return [UserVariable::FIELD_DEFAULT_UNIT_ID => $this->getUnit()->id];
	}
}
