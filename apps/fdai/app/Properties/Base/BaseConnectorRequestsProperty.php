<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseConnectorRequestsProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Most recent data that we\'ve requested from this data source ';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::CONNECTOR_REQUESTS;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::CONNECTOR_REQUESTS;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'connector_requests';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $title = 'Connector Requests';
	public $type = PhpTypes::STRING;

}
