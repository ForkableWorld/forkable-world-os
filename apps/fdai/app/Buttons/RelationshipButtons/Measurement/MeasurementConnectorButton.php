<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connector;
use App\Models\Measurement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MeasurementConnectorButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Measurement::FIELD_CONNECTOR_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_CONNECTOR_ID;
	public $ownerKeyName = Connector::FIELD_ID;
	public $qualifiedOwnerKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = Connector::class;
	public $methodName = 'connector';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Connector::COLOR;
	public $fontAwesome = Connector::FONT_AWESOME;
	public $id = 'connector-button';
	public $image = Connector::DEFAULT_IMAGE;
	public $text = 'Data Source';
	public $title = 'Data Source';
	public $tooltip = Connector::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
