<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\SentEmail;
class OAClientSentEmailsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = SentEmail::class;
	public $methodName = SentEmail::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = SentEmail::COLOR;
	public $fontAwesome = SentEmail::FONT_AWESOME;
	public $id = 'sent-emails-button';
	public $image = SentEmail::DEFAULT_IMAGE;
	public $text = 'Sent Emails';
	public $title = 'Sent Emails';
	public $tooltip = SentEmail::CLASS_DESCRIPTION;
}
