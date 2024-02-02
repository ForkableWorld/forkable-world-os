<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Astral\UnitBaseAstralResource;
use App\Traits\HasModel\HasUnit;
use App\Fields\Field;
use App\Fields\Select;
use App\Http\Requests\AstralRequest;
class BaseDefaultUnitIdProperty extends BaseUnitIdProperty {
	public const NAME = 'default_unit_id';
	public const NAME_SYNONYMS = [
		'default_unit_abbreviated_name',
		'unit_abbreviated_name',
		'unit_name',
		'unit',
	];
	public const SYNONYMS      = [
		'default_unit_id',
		'unit_id',
	];
	public $canBeChangedToNull = true;
	public $description = 'The default unit used for measurements.';
	public $name = self::NAME;
	public $title = 'Default Unit';
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return UnitBaseAstralResource::belongsTo($name ?? 'Unit', 'default_unit');
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return Select::make('Unit', $this->name)->options(function(){
			/** @var HasUnit $model */
			$req = AstralRequest::req();
			$model = $req->findModelOrFail();
			$opts = $model->getCompatibleUnitOptions();
			return $opts;
		})->displayUsingLabels();
	}
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
		if(!$this->shouldValidate()){
			return;
		}
		parent::validate();
	}
}
