<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Astral\Filters\VariableCategoryFilter;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseDefaultValueProperty;
use App\Fields\Field;
use App\Http\Requests\AstralRequest;
use App\VariableCategories\TreatmentsVariableCategory;
class TrackingReminderDefaultValueProperty extends BaseDefaultValueProperty
{
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getFloatField($name, function($value, $resource, $attribute){
            /** @var TrackingReminder $resource */
            return $value.$resource->getUnitAbbreviatedName();
        })->updateLink();
    }
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {
        $applyFilters = AstralRequest::getApplyFilters() ?? [];
        foreach($applyFilters as $applyFilter){
            $filter = $applyFilter->filter;
            if($filter instanceof VariableCategoryFilter){
                if($applyFilter->value === (string)TreatmentsVariableCategory::ID){
                    return true;
                }
            }
        }
        return false;
    }
    public function showOnDetail(): bool {return true;}
}
