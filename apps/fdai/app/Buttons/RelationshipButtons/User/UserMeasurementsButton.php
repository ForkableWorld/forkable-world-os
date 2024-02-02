<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
class UserMeasurementsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Measurement::class;
	public $methodName = Measurement::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Measurement::COLOR;
	public $fontAwesome = Measurement::FONT_AWESOME;
	public $id = 'measurements-button';
	public $image = Measurement::DEFAULT_IMAGE;
	public $text = 'Measurements';
	public $title = 'Measurements';
	public $tooltip = Measurement::CLASS_DESCRIPTION;
	/**
	 * @param $methodOrModel
	 * @param HasMany|null $relation
	 */
	public function __construct($methodOrModel, HasMany $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
