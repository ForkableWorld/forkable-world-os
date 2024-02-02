<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\GlobalVariableRelationship;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use Illuminate\Database\Eloquent\Relations\HasMany;
class GlobalVariableRelationshipCorrelationsButton extends HasManyRelationshipButton {
    public $interesting = true;
	public $parentClass = GlobalVariableRelationship::class;
	public $qualifiedParentKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = UserVariableRelationship::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'user_variable_relationships-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Individual User Studies';
	public $title = 'Individual User Studies';
	public $tooltip = "See individual user studies that were aggregated to create this analysis. ";
    public function __construct($methodOrModel, HasMany $relation = null){
        parent::__construct($methodOrModel, $relation);
    }
    public function getUrl(array $params = []): string{
        return parent::getUrl($params);
    }
}
