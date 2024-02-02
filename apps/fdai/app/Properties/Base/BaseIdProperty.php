<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\IsInt;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseIdProperty extends BaseProperty
{
    use IsInt;
	public $dbInput = 'integer,true,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Unique identifier for the record';
	public $example = 1;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ID;
	public $htmlType = 'text';
	public $image = ImageUrls::ID;
	public $importance = false;
	public $isOrderable = true;
	public $isPrimary = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'id';
    public $phpType = PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'ID';
	public $type = self::TYPE_INTEGER;

}
