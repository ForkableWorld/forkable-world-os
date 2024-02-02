<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Properties\Base\BaseIsPublicProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Types\BoolHelper;
class UserVariableIsPublicProperty extends BaseIsPublicProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return bool
     */
    public static function calculate($model): ?bool{
        $val = $model->getAttribute(static::NAME);
        if($model->isTestVariable()){
            $val = false;
        } elseif($model->getUser()->getShareAllData()){
            $val = true;
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * Set the default options for the filter.
     *
     * @return string
     */
    public function defaultFilter(): string{return BoolHelper::ALL_STRING;}
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
