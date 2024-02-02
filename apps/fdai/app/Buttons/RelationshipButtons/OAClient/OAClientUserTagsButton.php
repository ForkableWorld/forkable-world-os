<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\UserTag;
class OAClientUserTagsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = UserTag::class;
	public $methodName = UserTag::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'user-tags-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $text = 'User Tags';
	public $title = 'User Tags';
	public $tooltip = UserTag::CLASS_DESCRIPTION;
}
