<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\DataSources\QMDataSource;
use App\Models\Connection;
use App\Models\Measurement;
class MeasurementConnectionButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Measurement::FIELD_CONNECTION_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_CONNECTION_ID;
	public $ownerKeyName = Connection::FIELD_ID;
	public $qualifiedOwnerKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = Connection::class;
	public $methodName = 'connection';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Connection::COLOR;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $id = 'connection-button';
	public $image = Connection::DEFAULT_IMAGE;
	public $text = 'Data Source';
	public $title = 'Data Source';
	public $tooltip = Connection::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, QMDataSource $dataSource){
		parent::__construct($methodOrModel);
		$this->setTextAndTitle($this->relationshipTitle . ": " . $dataSource->getTitleAttribute());
		$this->setImage($dataSource->getImage());
		if(!$this->link){
			le('!$this->link');
		}
	}
}
