<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Application;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Application;
use App\Models\OAClient;
class ApplicationOAuthClientButton extends BelongsToRelationshipButton {
	public $interesting = false;
	public $foreignKeyName = OAClient::FIELD_ID;
	public $qualifiedForeignKeyName = Application::TABLE . '.' . Application::FIELD_CLIENT_ID;
	public $ownerKeyName = OAClient::FIELD_ID;
	public $qualifiedOwnerKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $childClass = Application::class;
	public $parentClass = Application::class;
	public $qualifiedParentKeyName = Application::TABLE . '.' . Application::FIELD_ID;
	public $relatedClass = OAClient::class;
	public $methodName = 'oa_client';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = OAClient::COLOR;
	public $fontAwesome = OAClient::FONT_AWESOME;
	public $id = 'oauth-client-button';
	public $image = OAClient::DEFAULT_IMAGE;
	public $text = 'OAuth Client';
	public $title = 'OAuth Client';
	public $tooltip = OAClient::CLASS_DESCRIPTION;
}
