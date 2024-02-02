<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\CtCondition;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCauseIdProperty extends BaseProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'cause_id';
	public $example = 1;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CLIENT_ID;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'cause_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Cause ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required|integer|min:-2147483648|max:2147483647';
    public static function getForeignClass(): string{
        return CtCondition::class;
    }
}
