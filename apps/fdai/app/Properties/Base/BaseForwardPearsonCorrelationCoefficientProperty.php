<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\Models\UserVariableRelationship;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Fields\Field;
class BaseForwardPearsonCorrelationCoefficientProperty extends BaseProperty{
	use IsFloat;
    public const EFFECT_SIZE_strongly_positive = 'strongly positive';
    public const EFFECT_SIZE_strongly_negative = 'strongly negative';
    public const EFFECT_SIZE_moderately_negative = 'moderately negative';
    public const EFFECT_SIZE_moderately_positive = 'moderately positive';
    public const EFFECT_SIZE_very_weakly_positive = 'very weakly positive';
    public const EFFECT_SIZE_non_existent = 'non-existent';
    public const EFFECT_SIZE_weakly_negative = 'weakly negative';
    public const EFFECT_SIZE_very_weakly_negative = 'very weakly negative';
    public const EFFECT_SIZE_weakly_positive = 'weakly positive';
    public $dbInput = 'float,10,4';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Pearson correlation coefficient between cause and effect measurements';
	public $example = 0.1093;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $maximum = 1;
	public $minimum = -1;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
    public const NAME = UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT;
    public $name = self::NAME;
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Predictive Coefficient';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required';
    /**
     * @param float $forwardPearsonCorrelationCoefficient
     * @return string
     */
    public static function generateEffectSize(float $forwardPearsonCorrelationCoefficient): string
    {
        $size = self::EFFECT_SIZE_strongly_positive;
        if ($forwardPearsonCorrelationCoefficient < .6) {
            $size = self::EFFECT_SIZE_moderately_positive;
        }
        if ($forwardPearsonCorrelationCoefficient < 0.3) {
            $size = self::EFFECT_SIZE_weakly_positive;
        }
        if ($forwardPearsonCorrelationCoefficient < 0.1) {
            $size = self::EFFECT_SIZE_very_weakly_positive;
        }
        if ($forwardPearsonCorrelationCoefficient < 0) {
            $size = self::EFFECT_SIZE_very_weakly_negative;
        }
        if ($forwardPearsonCorrelationCoefficient < -0.1) {
            $size = self::EFFECT_SIZE_weakly_negative;
        }
        if ($forwardPearsonCorrelationCoefficient < -0.3) {
            $size = self::EFFECT_SIZE_moderately_negative;
        }
        if ($forwardPearsonCorrelationCoefficient < -0.6) {
            $size = self::EFFECT_SIZE_strongly_negative;
        }
        if ($forwardPearsonCorrelationCoefficient === 0) {
            $size = self::EFFECT_SIZE_non_existent;
        }
        return $size;
    }
	public const SYNONYMS = [
	    'correlation_coefficient',
        UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT
    ];
    public static function applyRequestParamsToQuery(\Illuminate\Database\Query\Builder $qb): void{
        parent::applyRequestParamsToQuery($qb);
    }
    public function getField($resolveCallback = null, string $name = null): Field{
        return parent::getField($resolveCallback, $name);
    }
}
