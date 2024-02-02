<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseManualTrackingProperty;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\DataSources\Connectors\RescueTimeConnector;
class VariableManualTrackingProperty extends BaseManualTrackingProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function fixInvalidRecords(){
        self::disableManualTrackingIfNecessary(Variable::whereNameLike(VariableNameProperty::TIME_SPENT_PREFIX));
        self::disableManualTrackingIfNecessary(Variable::whereMostCommonSourceName(RescueTimeConnector::DISPLAY_NAME));
    }
    public static function whereInvalid(): Builder {
        return Variable::whereMostCommonSourceName(RescueTimeConnector::DISPLAY_NAME)
            ->where(self::NAME, 1);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     * @return string[]
     */
    protected static function disableManualTrackingIfNecessary(Builder $qb): array{
        $qb->where(self::NAME, 1);
        /** @var Variable[]|Collection $variables */
        $variables = $qb->get();
        $names = $variables->pluck(Variable::FIELD_NAME)->all();
        if($count = count($names)){
            QMLog::error("Fixing $count manual tracking variables where: ".
                \App\Logging\QMLog::print_r($qb->getQuery()->wheres, true).
                \App\Logging\QMLog::print_r($names, true));
            foreach($variables as $variable){
                $variable->manual_tracking = false;
                $variable->save(); // Do it this way to make sure we have validation performed
            }
        }
        return $names;
    }
    public function validate(): void {
        parent::validate();
        $value = $this->getDBValue();
        $v = $this->getVariable();
        if($value && stripos($v->name, VariableNameProperty::TIME_SPENT_PREFIX) !== false){
            if(DatabaseSeeder::isReprocessingSeed()){
                $this->setValue(false);
                return;
            }
            $this->throwException("should not be true for variables with name like ".
                VariableNameProperty::TIME_SPENT_PREFIX);
        }
        if($value && stripos($v->most_common_source_name, RescueTimeConnector::DISPLAY_NAME) !== false){
            $this->throwException("should not be true for variables with most_common_source_name like ".
                RescueTimeConnector::DISPLAY_NAME);
        }
    }
}
