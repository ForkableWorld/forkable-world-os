<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\Purchase;
class OAClientPurchasesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = Purchase::class;
	public $methodName = Purchase::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Purchase::COLOR;
	public $fontAwesome = Purchase::FONT_AWESOME;
	public $id = 'purchases-button';
	public $image = Purchase::DEFAULT_IMAGE;
	public $text = 'Purchases';
	public $title = 'Purchases';
	public $tooltip = Purchase::CLASS_DESCRIPTION;
}
