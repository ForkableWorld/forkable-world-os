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
class BasePostStatusProperty extends BaseProperty{
	use IsString;
    const PUBLISH = 'publish';
    public const STATUS_PRIVATE = 'private';
    public const STATUS_PUBLISH = 'publish';
    public const STATUS_PENDING = 'pending';
    public const POST_STATUSES = [
        BasePostStatusProperty::STATUS_PRIVATE,
        BasePostStatusProperty::STATUS_PUBLISH,
        BasePostStatusProperty::STATUS_AUTO_DRAFT,
        BasePostStatusProperty::STATUS_INHERIT,
        BasePostStatusProperty::STATUS_PENDING,
        BasePostStatusProperty::STATUS_TRASH,
    ];
    public const STATUS_TRASH = 'trash';
    public const STATUS_INHERIT = 'inherit';
    public const STATUS_FUTURE = 'future';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_AUTO_DRAFT = 'auto-draft';
    public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Status of the post, e.g. ‘draft’, ‘pending’, ‘private’, ‘publish’. Also a great WordPress <a href=\"https://poststatus.com/\" target=\"_blank\">news site</a>.';
	public $example = 'publish';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::POST;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'post_status';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|in:publish,future,draft,pending,private';
	public $title = 'Post Status';
	public $type = PhpTypes::STRING;
	public $validations = 'required|in:publish,future,draft,pending,private';

}
