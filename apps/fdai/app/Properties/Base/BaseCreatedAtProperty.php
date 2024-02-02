<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCreatedAtProperty extends BaseProperty{
	use IsDateTime;
    const DEFAULT_VALUE = "2000-01-01 00:00:00";
    public $dbInput = self::TYPE_DATETIME;
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'When the database record was created.';
	public $example = '2015-11-25 08:45:05';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::_AT;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::CLOCK;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'created_at';
	public $phpType = PhpTypes::STRING;
	public $title = 'Created';
	public $type = self::TYPE_DATETIME;
	public $validations = 'required';
}
