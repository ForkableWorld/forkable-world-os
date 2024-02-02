<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseTaggedUserVariableIdProperty extends BaseUserVariableIdProperty
{
	public $canBeChangedToNull = false;
	public $description = 'tagged_user_variable_id';
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'tagged_user_variable_id';
	public $title = 'Tagged User Variable ID';

}
