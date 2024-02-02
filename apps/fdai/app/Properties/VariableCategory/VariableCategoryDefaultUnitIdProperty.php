<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseDefaultUnitIdProperty;
use App\Slim\Model\QMUnit;
class VariableCategoryDefaultUnitIdProperty extends BaseDefaultUnitIdProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    public function getOptions(): array{
        return QMUnit::allOptions();
    }
}
