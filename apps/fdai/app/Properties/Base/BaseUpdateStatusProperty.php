<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Fields\Field;
use App\Fields\Status;
use OpenApi\Generator;
class BaseUpdateStatusProperty extends EnumProperty{
    public const IMPORT_STATUS_ERROR = 'ERROR';
    public const IMPORT_STATUS_UPDATED = 'UPDATED';
    public const IMPORT_STATUS_DISCONNECTED = 'DISCONNECTED';
    public const IMPORT_STATUS_IMPORTING = 'UPDATING';
    public const IMPORT_STATUS_WAITING = 'WAITING';
	public $dbInput = 'string,32';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Indicates whether a connector is currently updated.';
	public $example = 'UPDATED';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::TRACK_PRIMARY_OUTCOME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENTS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 32;
	public $name = self::NAME;
	public const NAME = 'update_status';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:32';
	public $title = 'Update Status';
	public $type = self::TYPE_ENUM;
	public $validations = 'required';
    public $enum = [
        self::IMPORT_STATUS_ERROR,
        self::IMPORT_STATUS_UPDATED,
        self::IMPORT_STATUS_DISCONNECTED,
        self::IMPORT_STATUS_IMPORTING,
        self::IMPORT_STATUS_WAITING,
    ];
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return Status|Field
     */
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusField($name, $resolveCallback);
    }
    public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusField($name, $resolveCallback);
    }
    public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusField($name, $resolveCallback);
    }
    public function getCreateField($resolveCallback = null, string $name = null): Field{
        return $this->getStatusField($name, $resolveCallback);
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return Status
     */
    protected function getStatusField(?string $name, $resolveCallback): Status{
        return Status::make($name ??$this->getTitleAttribute())
            ->loadingWhen([
                self::IMPORT_STATUS_WAITING,
                self::IMPORT_STATUS_IMPORTING,
            ])
            ->failedWhen([
                self::IMPORT_STATUS_ERROR,
                self::IMPORT_STATUS_DISCONNECTED,
            ]);
    }
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
