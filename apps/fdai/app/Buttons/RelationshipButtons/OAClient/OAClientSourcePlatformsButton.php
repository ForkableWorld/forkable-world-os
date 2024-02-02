<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\SourcePlatform;
class OAClientSourcePlatformsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = SourcePlatform::class;
	public $methodName = SourcePlatform::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = SourcePlatform::COLOR;
	public $fontAwesome = SourcePlatform::FONT_AWESOME;
	public $id = 'source-platforms-button';
	public $image = SourcePlatform::DEFAULT_IMAGE;
	public $text = 'Source Platforms';
	public $title = 'Source Platforms';
	public $tooltip = SourcePlatform::CLASS_DESCRIPTION;
}
