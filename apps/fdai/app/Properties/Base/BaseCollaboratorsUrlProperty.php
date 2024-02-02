<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCollaboratorsUrlProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: https://api.github.com/repos/mikepsinn/qm-api/collaborators{/collaborator}';
	public $example = 'https://api.github.com/repos/codenitive/laravel-oneauth/collaborators{/collaborator}';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::COLLABORATORS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATORS;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'collaborators_url';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'max:255';
	public $showOnDetail = true;
	public $title = 'Collaborators Url';
	public $type = 'string';
	public $validations = 'max:255';

}
