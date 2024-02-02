<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMostCommonSourceNameProperty;
use App\Variables\QMUserVariable;
class UserVariableMostCommonSourceNameProperty extends BaseMostCommonSourceNameProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use IsCalculated;
    /**
     * @param QMUserVariable $uv
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?string {
        $mostCommon = $uv->mostCommonFromMeasurementsWithTags(Measurement::FIELD_SOURCE_NAME);
        $uv->setAttribute(static::NAME, $mostCommon);
        return $mostCommon;
    }
    public function validate(): void {
        parent::validate();
        $val =$this->getDBValue();
        if(!$val){
            $uv = $this->getUserVariable();
            $dsc = $uv->data_sources_count;
            if($dsc){
                $measurements = $uv->getQMUserVariable()->getMeasurementsWithTags();
                if($measurements){
                    $this->throwException("there should be a most common source if we have data_sources_count");
                }
            }
        }
    }
}
