<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use OpenApi\Generator;
class BaseEarliestTaggedMeasurementStartAtProperty extends BaseProperty{
	use IsDateTime;
    use IsCalculated;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = "The data and time of the earliest measurement for this variable including those derived from tagged variables";
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'earliest_tagged_measurement_start_at';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = "Earliest Data";
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';
    public function validate(): void {
        parent::validate();
        $variable = $this->getParentModel();
        if(!$variable->measurementsAreSet()){return;}
        $measurements = $this->getMeasurementsWithTags();
        $val = $this->getDBValue();
        if($measurements && !$val){
            $this->throwException("should be ".QMMeasurement::getFirst($measurements)->getStartAt());
        }
        if(!$measurements && $val){
            $this->throwException("should be null because there are no measurements");
        }
    }
    /**
     * @param QMVariable|QMUserVariable $model
     * @return mixed
     */
    public static function calculate($model){
        if($measurements = $model->getMeasurementsWithTags()){
            $m = AnonymousMeasurement::getFirst($measurements);
            $early = $m->getStartAt();
        } else {
            $early = null;
        }
        $model->setEarliestTaggedMeasurementStartAtAttribute($early);
        return $early;
    }
    public function cannotBeChangedToNull(): bool {
        /** @var UserVariable $parent */
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        if($parent->earliest_non_tagged_measurement_start_at){
            return true; // We need to set this even if we don't have tags because it's used in finding variables to correlate with
        }
        $model = $this->getQMVariable();
        if($model->numberOfRawMeasurementsWithTagsJoinsChildren){return true;}
        // Too slow to get measurements all the time
        if($model->allTaggedMeasurementsAreSet()){return !empty($model->getMeasurementsWithTags());}
        return false;
    }
    /**
     * @return QMMeasurement[]
     */
    protected function getMeasurementsWithTags(): array {
        $model = $this->getQMVariable();
        $measurements = $model->getMeasurementsWithTags();
        return $measurements;
    }
    protected function getQMVariable(): QMVariable {
        return $this->getParentModel()->getDBModel();
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
