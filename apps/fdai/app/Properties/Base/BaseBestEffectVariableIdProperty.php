<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Variables\QMVariable;
class BaseBestEffectVariableIdProperty extends BaseEffectVariableIdProperty{
	public $description = 'The outcome variable that is most strongly predicted by the given predictor variable.';
	public $name = self::NAME;
	public const NAME = 'best_effect_variable_id';
	public $title = 'Best Effect Variable';
	public $canBeChangedToNull = true;
    use \App\Traits\PropertyTraits\IsCalculated;
    public const SYNONYMS = [
        'best_effect_variable_id',
    ];
    /**
     * @param QMVariable $uv
     * @return int
     */
    public static function calculate($uv){
        $best = $uv->getBestUserVariableRelationship();
        $uv->setAttribute(static::NAME, $best->id);
        return $best->id;
    }
}
