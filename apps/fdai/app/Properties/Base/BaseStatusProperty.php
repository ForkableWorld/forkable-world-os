<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
use App\Properties\User\UserStatusProperty;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Fields\Field;
use App\Fields\Status;
use OpenApi\Generator;
abstract class BaseStatusProperty extends EnumProperty {
	public $dbInput = 'string,25:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Status';
	public $example = 'CORRELATE';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::STATUS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::GET_PREVIEW_BUILDS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 25;
	public $name = self::NAME;
	public const NAME = 'status';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:25';
	public $title = 'Status';
	public $type = self::TYPE_ENUM;
	public $validations = 'nullable|max:25';
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return Status|Field
     */
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusField($name, $resolveCallback);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusFieldWithStartTime($name);
    }
    public function validate(): void {
        parent::validate();
        $val = $this->getDBValue();
        if(empty($val)){
            $this->throwException("should not be empty");
        }
    }
    /**
     * @param string|null $name
     * @return Status
     */
    protected function getStatusFieldWithStartTime(?string $name): Status{
        return $this->getStatusField($name, function($value, $resource, $attribute){
                $status = $value;
                /** @var User $resource */
                if($status === UserStatusProperty::STATUS_ANALYZING){
                    /** @var User $this */
                    $status .= " (started ".TimeHelper::timeSinceHumanString($resource->analysis_started_at).")";
                }
                if($status === UserStatusProperty::STATUS_WAITING){
                    /** @var User $this */
                    $status .= " (requested ".TimeHelper::timeSinceHumanString($resource->analysis_requested_at).")";
                }
                if($status === UserStatusProperty::STATUS_ERROR){
                    /** @var User $this */
                    $status .= " (failed ".TimeHelper::timeSinceHumanString($resource->analysis_started_at).")";
                }
                if($status === UserStatusProperty::STATUS_UPDATED){
                    /** @var User $this */
                    $status .= " (analyzed ".TimeHelper::timeSinceHumanString($resource->analysis_ended_at).")";
                }
                return $status;
            })->loadingWhen([
                UserStatusProperty::STATUS_WAITING,
                UserStatusProperty::STATUS_ANALYZING,
                null
            ])->failedWhen([
                UserStatusProperty::STATUS_ERROR
            ]);
    }
}
