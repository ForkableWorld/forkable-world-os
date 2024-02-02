<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseJenkinsJobProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,255:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'jenkins_job';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::JENKINS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::JENKINS;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'jenkins_job';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Jenkins Job';
	public $type = 'string';

}
