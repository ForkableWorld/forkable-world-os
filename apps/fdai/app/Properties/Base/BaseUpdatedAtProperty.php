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
class BaseUpdatedAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'When the record was last modified';
	public $example = '2020-07-07 07:15:21';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::CLOCK;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::CLOCK;
	public $importance = false;
    public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'updated_at';
	public $phpType = PhpTypes::STRING;
	public $readOnly = true;
	public $title = 'Updated';
	public $type = self::TYPE_DATETIME;
	public $validations = 'required';
	public const SYNONYMS = [
	    'lastUpdated',
    ];
}
