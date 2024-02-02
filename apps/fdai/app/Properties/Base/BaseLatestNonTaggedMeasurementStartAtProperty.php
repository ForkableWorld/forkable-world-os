<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsDateTime;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use OpenApi\Generator;
class BaseLatestNonTaggedMeasurementStartAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = 'The time of the latest raw measurement for this variable not derived from a tagged variable';
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
	public const NAME = 'latest_non_tagged_measurement_start_at';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Latest Non Tagged Measurement Start';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';
	use IsCalculated;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        /** @var UserVariable $uv */
        $uv = $this->getParentModel();
        $val = $this->getDBValue();
        $this->assertEarliestBeforeLatest($uv->earliest_non_tagged_measurement_start_at, $val);
        if(!$uv->measurementsAreSet()){return;}
        $measurements = $this->getMeasurements();
        if($measurements && !$val){
            $this->throwException("should be ".QMMeasurement::getFirst($measurements)->getStartAt());
        }
        if(!$measurements && $val){
            $dbm = $uv->getQMUserVariable();
            $combined = $dbm->getCombinedNewQMMeasurements();
            if(!$combined){
                $this->throwException("should be null because there are no measurements");
            }
        }
    }
    /**
     * @param QMUserVariable|QMCommonVariable $uv
     * @return mixed|null
     */
    public static function calculate($uv){
        $at = null;
        if($measurements = $uv->getQMMeasurements()){
            $last = QMMeasurement::last($measurements);
            $at = $last->getStartAt();
        }
        $uv->setAttribute(static::NAME,  $at);
        $uv->setLatestNonTaggedMeasurementStartAtAttribute($at);
        return $at;
    }
    public function cannotBeChangedToNull(): bool {
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getQMVariable();
        if(!empty($uv->getNumberOfMeasurements())){return true;}
        if(!$uv->measurementsAreSet()){return false;} // Too slow to get measurements all the time
        return !empty($uv->getQMMeasurements());
    }
    /**
     * @return QMMeasurement[]
     */
    protected function getMeasurements(): array {
        $model = $this->getQMVariable();
        $measurements = $model->getQMMeasurements();
        return $measurements;
    }
    protected function getQMVariable(): QMVariable {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getParentModel()->getDBModel();
    }
}
