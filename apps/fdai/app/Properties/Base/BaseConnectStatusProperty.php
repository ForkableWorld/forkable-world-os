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
class BaseConnectStatusProperty extends EnumProperty{
    public const CONNECT_STATUS_CONNECTED = 'CONNECTED';
    public const CONNECT_STATUS_DISCONNECTED = 'DISCONNECTED';
    public const CONNECT_STATUS_ERROR = 'ERROR';
    public const CONNECT_STATUS_EXPIRED = 'EXPIRED';
	public $dbInput = 'string,32';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Indicates whether a connector is currently connected to a service for a user.';
	public $example = 'CONNECTED';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CONNECTION;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CONNECTION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 32;
	public $name = self::NAME;
	public const NAME = 'connect_status';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:32';
	public $title = 'Connect Status';
	public $type = self::TYPE_ENUM;
	public $validations = 'required';
    public $enum = [
        self::CONNECT_STATUS_CONNECTED,
        self::CONNECT_STATUS_DISCONNECTED,
        self::CONNECT_STATUS_ERROR,
        self::CONNECT_STATUS_EXPIRED,
    ];
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
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
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
