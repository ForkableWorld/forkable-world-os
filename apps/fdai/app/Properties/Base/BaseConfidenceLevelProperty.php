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
use App\UI\QMColor;
use OpenApi\Generator;
class BaseConfidenceLevelProperty extends EnumProperty
{
    use IsCalculated;
    public const CONFIDENCE_LEVEL_HIGH = 'HIGH';
    public const CONFIDENCE_LEVEL_LOW = 'LOW';
    public const CONFIDENCE_LEVEL_MEDIUM = 'MEDIUM';
    public $canBeChangedToNull = false;
    public $required = true;
	public $dbInput = PhpTypes::STRING;
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'confidence_level';
	public $example = self::CONFIDENCE_LEVEL_HIGH;
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LEVEL_DOWN_ALT_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'confidence_level';
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Confidence Level';
	public $type = PhpTypes::STRING;
    public $enum = [
        self::CONFIDENCE_LEVEL_HIGH,
        self::CONFIDENCE_LEVEL_LOW,
        self::CONFIDENCE_LEVEL_MEDIUM,
    ];
    /**
     * @param string $level
     * @return string
     */
    public static function toColor(string $level): string{
        $colors = [
            self::CONFIDENCE_LEVEL_LOW    => QMColor::HEX_GOOGLE_RED,
            self::CONFIDENCE_LEVEL_MEDIUM => QMColor::HEX_GOOGLE_YELLOW,
            self::CONFIDENCE_LEVEL_HIGH   => QMColor::HEX_GOOGLE_GREEN,
        ];
        $color = $colors[$level];
        return $color;
    }
    /**
     * @param HasCorrelationCoefficient $c
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($c): string{
        $level = self::CONFIDENCE_LEVEL_LOW;
        if($p = $c->getPValue()){  // Slows down production requests
            if($p < 0.05){
                $level = self::CONFIDENCE_LEVEL_MEDIUM;
            }
            if($p < 0.01){
                $level = self::CONFIDENCE_LEVEL_HIGH;
            }
        }
        if($c->getNumberOfUsers() > 10 && $level !== self::CONFIDENCE_LEVEL_HIGH){
            $level = self::CONFIDENCE_LEVEL_MEDIUM;
        }
        $pairs = $c->getNumberOfPairs();
        if($pairs > 100 && $level !== self::CONFIDENCE_LEVEL_HIGH){
            $level = self::CONFIDENCE_LEVEL_MEDIUM;
        }
        if($c->getNumberOfUsers() > 100 || $pairs > 500){
            $level = self::CONFIDENCE_LEVEL_HIGH;
        }
        $c->setAttribute(static::NAME, $level);
        return $level;
    }
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
