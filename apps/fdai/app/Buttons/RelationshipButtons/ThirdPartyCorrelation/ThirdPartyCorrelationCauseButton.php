<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ThirdPartyCorrelation;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\ThirdPartyCorrelation;
class ThirdPartyCorrelationCauseButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = ThirdPartyCorrelation::FIELD_CAUSE_ID;
	public $qualifiedForeignKeyName = ThirdPartyCorrelation::TABLE.'.'.ThirdPartyCorrelation::FIELD_CAUSE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE.'.'.Variable::FIELD_ID;
	public $childClass = ThirdPartyCorrelation::class;
	public $parentClass = ThirdPartyCorrelation::class;
	public $qualifiedParentKeyName = ThirdPartyCorrelation::TABLE.'.'.ThirdPartyCorrelation::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'cause';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'cause-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Cause';
	public $title = 'Cause';
	public $tooltip = Variable::CLASS_DESCRIPTION;

}
