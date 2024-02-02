<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\Study;
class OAClientStudiesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = Study::class;
	public $methodName = Study::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Study::COLOR;
	public $fontAwesome = Study::FONT_AWESOME;
	public $id = 'studies-button';
	public $image = Study::DEFAULT_IMAGE;
	public $text = Study::CLASS_CATEGORY;
	public $title = Study::CLASS_CATEGORY;
	public $tooltip = Study::CLASS_DESCRIPTION;
}
