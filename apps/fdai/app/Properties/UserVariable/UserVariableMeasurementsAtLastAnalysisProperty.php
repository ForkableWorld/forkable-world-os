<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMeasurementsAtLastAnalysisProperty;
class UserVariableMeasurementsAtLastAnalysisProperty extends BaseMeasurementsAtLastAnalysisProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $uv = $this->getUserVariable();
        $withTags = $uv->number_of_raw_measurements_with_tags_joins_children;
        $atLastAnalysis = $this->getDBValue();
        if($atLastAnalysis && $atLastAnalysis > $withTags){
            $this->throwException("MEASUREMENTS_AT_LAST_ANALYSIS $atLastAnalysis > $withTags NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN");
        }
    }
}
