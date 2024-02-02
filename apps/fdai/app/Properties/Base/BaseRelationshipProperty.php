<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseRelationshipProperty extends EnumProperty{
    use IsCalculated;
    const POSITIVE = 'POSITIVE';
    const NEGATIVE = 'NEGATIVE';
    const NONE = 'NONE';
    public $canBeChangedToNull = false;
    public $required = true;
    public $dbInput = PhpTypes::STRING;
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'relationship';
	public $example = 'POSITIVE';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::TERM_RELATIONSHIP;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::TERM_RELATIONSHIP;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'relationship';
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Relationship';
	public $type = PhpTypes::STRING;
    public $enum = [
        self::POSITIVE,
        self::NEGATIVE,
        self::NONE
    ];
    /**
     * @param \App\Traits\HasCorrelationCoefficient $model
     * @return string
     */
    public static function calculate($model): string{
        $cc = $model->getCorrelationCoefficient();
        if($cc > 0.1){
            $rel = self::POSITIVE;
        } elseif($cc < 0.1){
            $rel = self::NEGATIVE;
        } else{
            $rel = self::NONE;
        }
        $model->setAttribute(static::NAME, $rel);
        return $rel;
    }
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
