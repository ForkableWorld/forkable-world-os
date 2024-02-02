<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCauseNumberOfRawMeasurementsProperty extends BaseProperty{
	use IsInt;
    public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'cause_number_of_raw_measurements';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = self::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN;
	public $name = self::NAME;
	public const NAME = 'cause_number_of_raw_measurements';
	public $canBeChangedToNull = false;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:0|max:2147483647';
	public $title = 'Cause Raw Measurements';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
	public const MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 1;
    /**
     * @param int $id
     * @return BaseModel
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\AlreadyAnalyzingException
     * @throws \App\Exceptions\DuplicateFailedAnalysisException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function handleTooSmall(int $id): BaseModel{
        $c = static::findParent($id);
        $c->analyze(__FUNCTION__);
    }
    /**
     * @param int $id
     * @return UserVariableRelationship
     */
    public static function findParent($id): ?BaseModel{
        return parent::findParent($id);
    }
}
