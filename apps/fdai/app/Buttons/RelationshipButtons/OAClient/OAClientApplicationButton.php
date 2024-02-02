<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasOneRelationshipButton;
use App\Models\Application;
use App\Models\OAClient;
class OAClientApplicationButton extends HasOneRelationshipButton {
	public $interesting = true;
	public $relatedClass = Application::class;
	public $foreignKeyName = Application::FIELD_CLIENT_ID;
	public $qualifiedForeignKeyName = Application::TABLE . '.' . Application::FIELD_CLIENT_ID;
	public $localKeyName = Application::FIELD_CLIENT_ID;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $methodName = 'application';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasOne';
	public $color = Application::COLOR;
	public $fontAwesome = Application::FONT_AWESOME;
	public $id = 'application-button';
	public $image = Application::DEFAULT_IMAGE;
	public $text = 'Application';
	public $title = 'Application';
	public $tooltip = Application::CLASS_DESCRIPTION;
}
