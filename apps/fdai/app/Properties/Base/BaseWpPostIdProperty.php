<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\WpPost;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseWpPostIdProperty extends BaseIntegerIdProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The associated page id';
	public $example = null; // Keep null
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::POST;
	public $htmlType = 'text';
	public $image = ImageUrls::LINK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
    public bool $deprecated = true;
	public const NAME = 'wp_post_id';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|numeric|min:1';
	public $title = 'Post ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|numeric|min:1';
    public static function getForeignClass(): string{
        return WpPost::class;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {
		return false; // There's some Corcel conflict and we don't need posts anyway
		return (bool)$this->getDBValue();
	}
}
