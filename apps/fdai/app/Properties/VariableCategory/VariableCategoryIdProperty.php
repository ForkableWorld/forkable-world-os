<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class VariableCategoryIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'variable_category_id',
        'id',
    ];
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
