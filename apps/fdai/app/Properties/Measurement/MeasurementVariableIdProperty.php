<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Models\Variable;
use App\Properties\Base\BaseVariableIdProperty;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Variables\QMCommonVariable;
class MeasurementVariableIdProperty extends BaseVariableIdProperty {
	use MeasurementProperty, ForeignKeyIdTrait;
	public $parentClass = Measurement::class;
	public $table = Measurement::TABLE;
	/**
	 * @param $data
	 * @return Variable
	 */
	public static function findRelated($data): ?Variable{
		$id = static::pluckOrDefault($data);
		if(!$id){
			return null;
		}
		$v = Variable::findInMemoryOrDB($id);
		if(!$v){
			le("Could not find variable with id $id");
		}
		return $v;
	}
	public function showOnCreate(): bool{ return true; }
	/**
	 * @param $data
	 * @return int
	 */
	public static function findUnitId($data): int{
		$id = static::pluck($data);
		if(!$id){
			throw new \LogicException("Could not get variable id");
		}
		$qmv = QMCommonVariable::find($id);
		return $qmv->getUnitIdAttribute();
	}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return void
	 */
	public function pluckAndSetDBValue($data, bool $fallback = false){
		$val = static::pluckOrDefault($data);
		if(!$val){
			$uv = $this->getUserVariable();
			$val = $uv->variable_id;
		}
		$this->setRawAttribute($val);
	}
}
