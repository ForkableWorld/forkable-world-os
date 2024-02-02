<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\PropertyTraits\EnumProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseStrengthLevelProperty extends EnumProperty{
    use IsCalculated;
    const STRENGTH_VERY_WEAK = 'VERY WEAK';
    const STRENGTH_WEAK = 'WEAK';
    const STRENGTH_MODERATE = 'MODERATE';
    const STRENGTH_STRONG = 'STRONG';
    const STRENGTH_VERY_STRONG = 'VERY STRONG';
    public $canBeChangedToNull = false;
    public $required = true;
	public $dbInput = PhpTypes::STRING;
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'strength_level';
	public $example = 'VERY STRONG';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LEVEL_DOWN_ALT_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'strength_level';
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Strength Level';
	public $type = PhpTypes::STRING;
	public $enum = [
	    self::STRENGTH_MODERATE,
        self::STRENGTH_STRONG,
        self::STRENGTH_VERY_STRONG,
        self::STRENGTH_VERY_WEAK,
        self::STRENGTH_WEAK,
    ];
    /**
     * @param HasCorrelationCoefficient $c
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($c): string{
        $cc = $c->getCorrelationCoefficient();
		if($cc === null){
			$cc = $c->getCorrelationCoefficient();
		}
	    if($cc === null){
		    le('no correlation coefficient', [get_class($c), $c]);
	    }
        $strength = self::STRENGTH_VERY_WEAK;
        $abs = abs($cc);
        if($abs > 0.2){$strength = self::STRENGTH_WEAK;}
        if($abs > 0.4){$strength = self::STRENGTH_MODERATE;}
        if($abs > 0.6){$strength = self::STRENGTH_STRONG;}
        if($abs > 0.8){$strength = self::STRENGTH_VERY_STRONG;}
        $c->setAttribute(static::NAME, $strength);
        return $strength;
    }
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
