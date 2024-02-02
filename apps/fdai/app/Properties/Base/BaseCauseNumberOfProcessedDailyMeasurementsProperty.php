<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\Models\UserVariableRelationship;
use App\Storage\DB\QMQB;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCauseNumberOfProcessedDailyMeasurementsProperty extends BaseProperty{
	use IsInt;
    public const MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 4;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
    public $description = 'Number of predictor variable measurements (aggregated daily) used in the analysis. ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = self::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN;
	public $name = self::NAME;
	public const NAME = 'cause_number_of_processed_daily_measurements';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:5|max:2147483647';
	public $title = 'Cause Processed Daily Measurements';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
	public static function fixTooSmall(): array{
        $ids = parent::getIdsWhereTooSmall();
        foreach($ids as $id){
            $c = UserVariableRelationship::find($id);
            $c->softDelete();
        }
    }
    public static function whereTooSmall(): QMQB {
        return static::whereTooSmall();
    }
    public function getMinimum(): ?float{
	    $existingValue = $this->getRawOriginalValue();
	    $min = parent::getMinimum();
	    if($existingValue && $existingValue < $min){
	        $model = $this->getParentModel();
            $model->logError("Existing $this->name is less than min $min");
	        $min = $existingValue;
        }
        return $min;
    }
}
