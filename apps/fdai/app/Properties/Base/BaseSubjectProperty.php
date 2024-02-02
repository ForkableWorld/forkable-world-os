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
class BaseSubjectProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,78';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'A Subject Line is the introduction that identifies the emails intent.
                    This subject line, displayed to the email user or recipient when they look at their list of messages in their inbox,
                    should tell the recipient what the message is about, what the sender wants to convey.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'subject';
	public $phpType = PhpTypes::STRING;
	public $title = 'Subject';
	public $type = PhpTypes::STRING;

}
