<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseEffectUserVariableIdProperty extends BaseUserVariableIdProperty{
    const RELATIONSHIP_TITLE = 'Effect';
    const RELATIONSHIP_METHOD = 'effect_user_variable';
    public $description = 'The individual user variable considered the outcome in this analysis.';
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $canBeChangedToNull = false;
	public $name = self::NAME;
	public const NAME = 'effect_user_variable_id';
	public $title = 'Outcome User Variable';
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
