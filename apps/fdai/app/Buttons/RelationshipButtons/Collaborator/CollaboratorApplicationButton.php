<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Collaborator;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Application;
use App\Models\Collaborator;
class CollaboratorApplicationButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Collaborator::FIELD_APP_ID;
	public $qualifiedForeignKeyName = Collaborator::TABLE . '.' . Collaborator::FIELD_APP_ID;
	public $ownerKeyName = Application::FIELD_ID;
	public $qualifiedOwnerKeyName = Application::TABLE . '.' . Application::FIELD_ID;
	public $childClass = Collaborator::class;
	public $parentClass = Collaborator::class;
	public $qualifiedParentKeyName = Collaborator::TABLE . '.' . Collaborator::FIELD_ID;
	public $relatedClass = Application::class;
	public $methodName = 'application';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Application::COLOR;
	public $fontAwesome = Application::FONT_AWESOME;
	public $id = 'application-button';
	public $image = Application::DEFAULT_IMAGE;
	public $text = 'Application';
	public $title = 'Application';
	public $tooltip = Application::CLASS_DESCRIPTION;
}
