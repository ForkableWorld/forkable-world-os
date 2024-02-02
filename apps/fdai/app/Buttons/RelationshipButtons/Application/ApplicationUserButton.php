<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Application;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Application;
use App\Models\User;
class ApplicationUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Application::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Application::TABLE . '.' . Application::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Application::class;
	public $parentClass = Application::class;
	public $qualifiedParentKeyName = Application::TABLE . '.' . Application::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Application::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
