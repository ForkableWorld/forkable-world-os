<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\ForeignKeyIdTrait;
class BasePrimaryOutcomeVariableIdProperty extends BaseVariableIdProperty{
	use ForeignKeyIdTrait;
	public $description = 'primary_outcome_variable_id';
	public $name = self::NAME;
	public const NAME = 'primary_outcome_variable_id';
	public $title = 'Primary Outcome Variable';
	public $canBeChangedToNull = true;
}
