<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connector;
use App\Models\WpPost;
class WpPostConnectorsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Connector::class;
	public $methodName = Connector::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Connector::COLOR;
	public $fontAwesome = Connector::FONT_AWESOME;
	public $id = 'connectors-button';
	public $image = Connector::DEFAULT_IMAGE;
	public $text = 'Connectors';
	public $title = 'Connectors';
	public $tooltip = Connector::CLASS_DESCRIPTION;
}
