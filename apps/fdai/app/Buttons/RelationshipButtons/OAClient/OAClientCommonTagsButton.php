<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CommonTag;
use App\Models\OAClient;
class OAClientCommonTagsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = CommonTag::class;
	public $methodName = CommonTag::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'common-tags-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $text = 'Common Tags';
	public $title = 'Common Tags';
	public $tooltip = CommonTag::CLASS_DESCRIPTION;
}
