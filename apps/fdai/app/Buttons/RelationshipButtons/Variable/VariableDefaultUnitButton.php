<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Unit;
use App\Models\Variable;
use App\Slim\Model\QMUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VariableDefaultUnitButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Variable::FIELD_DEFAULT_UNIT_ID;
	public $qualifiedForeignKeyName = Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID;
	public $ownerKeyName = Unit::FIELD_ID;
	public $qualifiedOwnerKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $childClass = Variable::class;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = 'default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'default-unit-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Default Unit';
	public $title = 'Default Unit';
	public $tooltip = Unit::CLASS_DESCRIPTION;
	/**
	 * @param $methodOrModel
	 * @param BelongsTo|null $relation
	 */
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
	/**
	 * @return Variable|Model
	 */
	public function getVariable(): Variable{
		return $this->getButtonRelation()->getChild();
	}
	public function getQMUnit(): QMUnit{
		return $this->getVariable()->getQMUnit();
	}
	public function getRelated(): QMUnit{
		return $this->getQMUnit();
	}
	public function getRelatedIds(): array{
		return [Variable::FIELD_DEFAULT_UNIT_ID => $this->getQMUnit()->id];
	}
}
