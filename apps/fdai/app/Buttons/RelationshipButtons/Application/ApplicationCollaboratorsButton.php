<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Application;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Application;
use App\Models\Collaborator;
class ApplicationCollaboratorsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Application::class;
	public $qualifiedParentKeyName = Application::TABLE . '.' . Application::FIELD_ID;
	public $relatedClass = Collaborator::class;
	public $methodName = Collaborator::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Collaborator::COLOR;
	public $fontAwesome = Collaborator::FONT_AWESOME;
	public $id = 'collaborators-button';
	public $image = Collaborator::DEFAULT_IMAGE;
	public $text = 'Collaborators';
	public $title = 'Collaborators';
	public $tooltip = Collaborator::CLASS_DESCRIPTION;
}
