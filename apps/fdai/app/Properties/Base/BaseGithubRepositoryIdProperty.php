<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseGithubRepositoryIdProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'Github repository id Example: 158861117';
	public $example = 4304250;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::GITHUB;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::DATA_SOURCES_GITHUB_SMALL_MFUESC;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'github_repository_id';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:-2147483648|max:2147483647|unique:github_repositories,github_repository_id';
	public $showOnDetail = true;
	public $title = 'Github Repository ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required|integer|min:-2147483648|max:2147483647|unique:github_repositories,github_repository_id';

}
