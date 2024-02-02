<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connector;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connector;
use App\Models\ConnectorImport;
class ConnectorConnectorImportsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Connector::class;
	public $qualifiedParentKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
	public $relatedClass = ConnectorImport::class;
	public $methodName = ConnectorImport::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ConnectorImport::COLOR;
	public $fontAwesome = ConnectorImport::FONT_AWESOME;
	public $id = 'connector-imports-button';
	public $image = ConnectorImport::DEFAULT_IMAGE;
	public $text = 'Connector Imports';
	public $title = 'Connector Imports';
	public $tooltip = ConnectorImport::CLASS_DESCRIPTION;
}
