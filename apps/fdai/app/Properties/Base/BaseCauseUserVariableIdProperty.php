<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseCauseUserVariableIdProperty extends BaseUserVariableIdProperty{
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
    public $description = 'The individual user variable considered the predictor in this analysis.';
	public $example = 52849;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'cause_user_variable_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'Predictor User Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
