<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Application;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAppIdProperty extends BaseProperty
{
    use ForeignKeyIdTrait;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'app_id';
	public $example = 311;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::HAS_ANDROID_APP;
	public $htmlType = 'text';
	public $image = ImageUrls::HAS_ANDROID_APP;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'app_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $showOnDetail = true;
	public $title = 'App ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public static function getForeignClass(): string{return Application::class;}
}
