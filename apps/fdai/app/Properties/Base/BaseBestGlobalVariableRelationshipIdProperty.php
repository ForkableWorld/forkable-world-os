<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
class BaseBestGlobalVariableRelationshipIdProperty extends BaseGlobalVariableRelationshipIdProperty{
	public $description = 'The global variable relationship including this variable with the greatest strength and statistical power';
	public $name = self::NAME;
	public const NAME = 'best_global_variable_relationship_id';
	public $title = 'Best Global Variable Relationship';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
}
