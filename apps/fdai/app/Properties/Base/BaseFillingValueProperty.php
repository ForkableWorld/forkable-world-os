<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Storage\DB\QMQB;
use App\Traits\PropertyTraits\IsHyperParameter;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Slim\Model\QMUnit;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
class BaseFillingValueProperty extends BaseValueProperty{
	use IsFloat;
    use IsHyperParameter;
	public $description = 'The filling value is substituted used when data is missing if the filling type is set to value.';
	public $example = -1;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'filling_value';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Filling Value';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    /**
     * @param Variable|UserVariable|QMVariable $v
     * @return bool
     */
    public static function hasFillingValue($v): bool {
        $type = $v->getFillingTypeAttribute();
        return in_array($type, [BaseFillingTypeProperty::FILLING_TYPE_ZERO, BaseFillingTypeProperty::FILLING_TYPE_VALUE]);
    }
    /**
     * @param string $type
     * @param float|null $value
     * @return mixed
     */
    public static function fromType(string $type, float $value = null){
        if($type === BaseFillingTypeProperty::FILLING_TYPE_NONE){
            return null;
        }
        if($type === BaseFillingTypeProperty::FILLING_TYPE_ZERO){
            return 0.0;
        }
        return $value;
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
    }
    /**
     * @param int|float $currentFillingValue
     * @param QMUnit $unit
     * @return float
     * Variable categories aren't a good place for filling value setting because we could have a rating Social
     * Interaction variable and hours from Rescuetime
     */
    public static function getFillingValueOrFallback($currentFillingValue,
                                                     QMUnit $unit)
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($currentFillingValue != -1) {
            return $currentFillingValue;
        }
        $currentFillingValue = $unit->getFillingValueAttribute();
        return $currentFillingValue;
    }
    public static function whereTooSmall(): ?QMQB{
        $table = static::getTable();
        $name = static::NAME;
        return QMUserVariable::qb()
            ->whereNotNull($table.'.'.$name)
            ->whereNotNull(Variable::TABLE.'.'.Variable::FIELD_MINIMUM_ALLOWED_VALUE)
            ->where($table.'.'.$name, "<>", -1)
            ->whereRaw($table.'.'.$name. " < ".Variable::TABLE.'.'.Variable::FIELD_MINIMUM_ALLOWED_VALUE);
    }
    public static function fillingValueToType(?float $val): string {
        if($val === null || (int)$val === (int)-1){
            return BaseFillingTypeProperty::FILLING_TYPE_NONE;
        } elseif($val == 0){
           return BaseFillingTypeProperty::FILLING_TYPE_ZERO;
        } else {
            return BaseFillingTypeProperty::FILLING_TYPE_VALUE;
        }
    }
    protected function validateMin(): void{
        $val = $this->getDBValue();
        if($val != -1){
            parent::validateMin();
        }
    }
}
