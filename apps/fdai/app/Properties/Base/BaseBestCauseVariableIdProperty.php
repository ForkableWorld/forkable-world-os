<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Fields\BelongsTo;
class BaseBestCauseVariableIdProperty extends BaseCauseVariableIdProperty{
	public $description = 'The variable that is most predictive of the given outcome variable.';
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $name = self::NAME;
	public const NAME = 'best_cause_variable_id';
	public $title = 'Best Cause Variable';
	public $canBeChangedToNull = true;
    public const SYNONYMS = [
        'best_cause_variable_id',
    ];
    public static function belongsTo(string $name = null):BelongsTo{
        $p = new static();
        return $p->getBelongsToField($name);
    }
}
