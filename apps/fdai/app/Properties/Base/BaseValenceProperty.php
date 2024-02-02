<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\InvalidVariableSettingException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\VariableCategories\InvestmentStrategiesVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\Variables\QMVariable;
use OpenApi\Generator;
class BaseValenceProperty extends EnumProperty{
    public const VALENCE_NEGATIVE = 'negative';
    public const VALENCE_POSITIVE = 'positive';
    public const VALENCE_NEUTRAL = 'neutral';
    public $dbInput = 'string:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Set the valence positive if more is better for all the variables in the category, negative if more is bad, and neutral if none of the variables have such a valence. Valence is null if there is not a consistent valence for all variables in the category.';
	public $enum = [self::VALENCE_POSITIVE, self::VALENCE_NEGATIVE, self::VALENCE_NEUTRAL];
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'valence';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable';
	public $title = 'Valence';
	public $type = self::TYPE_ENUM;
	public $canBeChangedToNull = true;
	public $validations = 'nullable';
    /**
     * @param Variable|UserVariable|QMVariable $v
     * @return string
     */
    public static function generate($v): ?string{
        $catId = $v->getVariableCategoryId();
        if($catId === SymptomsVariableCategory::ID){
            return BaseValenceProperty::VALENCE_NEGATIVE;
        } elseif ($catId === EconomicIndicatorsVariableCategory::ID){
            return BaseValenceProperty::VALENCE_POSITIVE;
        } elseif ($catId === InvestmentStrategiesVariableCategory::ID){
            return BaseValenceProperty::VALENCE_POSITIVE;
        }
        return null;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
//        /** @var Variable $model */
//        $model = $this->getParentModel();
//        if(!$model->isRating()){
//            $newValence = $this->getDBValue();
//            $message = "valence should not be set on a non-rating variable";
//            if($ex = false){ // TODO: Maybe start throwing exceptions?
//                throw new InvalidVariableSettingException($model, $this->name, $newValence,
//                    $message);
//            } else {
//                $this->logError($message);
//                $this->setRawAttribute(null);
//            }
//        }
    }
    public static function fixInvalidRecords(){
	    Variable::whereNameLike("Daily Return")
            ->update([Variable::FIELD_VALENCE => self::VALENCE_POSITIVE]);
        return parent::fixInvalidRecords();
    }
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
