<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseDeletionReasonProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,280:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'The reason the variable was deleted.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::REASON_FOR_ANALYSIS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::REASON_FOR_ANALYSIS;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'deletion_reason';
	public $phpType = PhpTypes::STRING;
	public $title = 'Deletion Reason';
	public $type = PhpTypes::STRING;
	public $required = false;
	public function isRequired(): bool{
	    $parent = $this->getParentModel();
	    if($parent->deleted_at){
	        return true;
        }
        return parent::isRequired();
    }
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if($parent->deleted_at){
            return true;
        }
        return false;
    }
	public function showOnCreate(): bool{return false;}
}
