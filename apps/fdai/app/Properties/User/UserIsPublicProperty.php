<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\ModelTraits\UserTrait;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Types\BoolHelper;
class UserIsPublicProperty extends BaseIsPublicProperty
{
    use UserProperty;
    use IsCalculated;
    public $table = User::TABLE;
    public $parentClass = User::class;
    /**
     * @param UserTrait $model
     * @return bool
     */
    public static function calculate($model): bool{
        $val = $model->getShareAllData();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * Set the default options for the filter.
     * @return string
     */
    public function defaultFilter(): string{return BoolHelper::ALL_STRING;}
    public function showOnIndex(): bool{return false;}
    public function showOnDetail(): bool{return false;}
    public function showOnCreate(): bool{return false;}
    public function showOnUpdate(): bool{return false;}
}
