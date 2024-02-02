<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseRequestBodyProperty extends BaseProperty
{
    use IsJsonEncoded;
	public $canBeChangedToNull = true;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'request_body';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::CONNECTOR_REQUEST;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::CONNECTOR_REQUEST;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'request_body';
            	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Request Body';
	public $type = PhpTypes::STRING;

}
