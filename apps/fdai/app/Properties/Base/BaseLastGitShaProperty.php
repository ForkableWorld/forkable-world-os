<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLastGitShaProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,191:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'last_git_sha';
	public $minLength = 12;
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::CARD_LAST_FOUR;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'last_git_sha';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Last Git Sha';
	public $type = 'string';

}
