<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Doctrine\DBAL\Types\Types;
use App\Fields\Field;
class BaseCodeProperty extends BaseProperty
{
    use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'code';
	public $example = 200;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::OAUTH_AUTHORIZATION_CODE;
	public $htmlType = 'text';
	public $image = ImageUrls::OAUTH_AUTHORIZATION_CODE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'code';
    public $phpType = PhpTypes::INT;
	public $rules = 'required|integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Code';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public function getExample(): int{
        return 200;
    }

}
