<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseVariableCategoryIdProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
class UserVariableVariableCategoryIdProperty extends BaseVariableCategoryIdProperty
{
    use UserVariableProperty, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public $canBeChangedToNull = true;
    public const SYNONYMS = [
        'user_variable_variable_category_id',
        'user_variable_category_id',
        'common_variable_category_id',
        'variable_category_id',
        'category_id',
    ];
    /**
     * @param UserVariable|QMUserVariable $model
     * @return string
     */
    public static function calculate($model){
        return parent::calculate($model);
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $uv = $this->getVariable();
        if(stripos($uv->name, 'Productively') !== false){
            $this->throwException("Why are we changing VARIABLE_CATEGORY_ID for $uv->name?");
        }
    }
    public static function updateFromData($data, BaseModel $uv){
        return parent::updateFromData($data, $uv);
    }
    public static function pluckOrDefault($data){
        $id = parent::pluckOrDefault($data);
        if(is_string($id)){
            if($cat = QMVariableCategory::find($id)){
                return $cat->id;
            }
        }
		if($id && !is_int($id)){le('$id && !is_int($id)');}
        return $id;
    }
}
