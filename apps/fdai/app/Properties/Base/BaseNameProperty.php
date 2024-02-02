<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\BaseModel;
use App\Traits\PropertyTraits\IsString;
use App\Models\Variable;
use App\Types\PhpTypes;
use App\Types\QMArr;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Fields\Field;
use App\Slim\Model\DBModel;
use OpenApi\Generator;
class BaseNameProperty extends BaseProperty{
	use IsString;
	public const SYNONYMS = [BaseDisplayNameProperty::NAME];
    const MIN_LENGTH = 3;
    const MAX_LENGTH = 125;
    public $dbInput = 'string,125';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Name';
	public $example = 'Daily Average Grade';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::DISPLAY_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::NAME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = self::MAX_LENGTH;
	public $minLength = self::MIN_LENGTH;
	public $name = self::NAME;
	public const NAME = 'name';
    public $order = "00";
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:125|min:3';
	public $title = 'Name';
	public $type = PhpTypes::STRING;
	public $validations = 'required';
	public $showOnIndex = true;
	public $shouldNotEqual = [
		"0"
	];
	/**
	 * @param bool $throwException
	 * @return string|null
	 */
	public static function fromRequest(bool $throwException = false): ?string{
        $val = parent::fromRequest($throwException);
        if(!$val){return null;}
        return str_replace("+", " ", $val);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getDetailLinkTextField($name,$resolveCallback ?? function ($value, $resource, $attribute) {
            /** @var Variable $this */
            return $resource->getDisplayNameAttribute();
        });
    }
    /**
     * @param DBModel|BaseModel|array|object $data
     * @return string|null
     */
    public static function pluck($data): ?string{
        if(static::NAME === BaseNameProperty::NAME &&
            is_object($data) &&
            method_exists($data, 'getNameAttribute')){
            return $data->getNameAttribute();
        }else{
            return parent::pluck($data);
        }
    }
    /**
     * @param $arr
     * @return string[]
     */
    public static function pluckNames($arr): array{
		$first = QMArr::first($arr);
		if(method_exists($first, 'getName')){
			return self::pluckNames($arr);
		}
		return static::pluckArray($arr);
    }
    public static function fromIds(array $ids): array{
        $models = [];
        foreach($ids as $id){$models = static::findParent($id);}
        return static::pluckNames($models);
    }
}
