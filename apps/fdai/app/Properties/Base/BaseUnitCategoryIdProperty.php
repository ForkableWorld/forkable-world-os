<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UnitCategory;
use App\Astral\UnitCategoryBaseAstralResource;
use App\Traits\ForeignKeyIdTrait;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\UnitCategories\RatingUnitCategory;
class BaseUnitCategoryIdProperty extends BaseIntegerIdProperty{
    use ForeignKeyIdTrait;
	public $dbInput = self::TYPE_BOOLEAN;
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Unit category';
	public $example = RatingUnitCategory::ID;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::UNIT_CATEGORY;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::UNIT_CATEGORY;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'unit_category_id';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'required|numeric';
	public $title = 'Unit Category';
	public $type = self::TYPE_BOOLEAN;
	public $validations = 'required|numeric';
    /**
     * @return UnitCategory
     */
    public static function getForeignClass(): string{
        return UnitCategory::class;
    }
    public function getIndexField($resolveCallback = null, string $name = null): \App\Fields\Field{
        return UnitCategoryBaseAstralResource::belongsTo($name ?? "Unit Category", 'unit_category');
    }
}
