<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseParentIdProperty extends BaseVariableIdProperty{
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'ID of the parent variable if this variable has any parent';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'parent_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Parent';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:1|max:2147483647';
    public const NAME_SYNONYMS = [
        'parent_variable_name',
        'parent_variable',
    ];
    public static function getSynonyms(): array
    {
        return [static::NAME, 'parentId'];
    }

    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
    public static function pluck($data): ?int
    {
        return parent::pluck($data);
    }
}
