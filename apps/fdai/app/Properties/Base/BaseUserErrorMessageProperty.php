<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Logging\QMLog;
use App\Storage\Memory;
use App\Traits\PropertyTraits\IsHtml;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseUserErrorMessageProperty extends BaseProperty{
	use IsHtml;
	public const SHOULD_NOT_CONTAIN_STRINGS = [
		'storeCredentials ',
		'recent import never',
		'Undefined variable',
		'Exception',
	];
	protected $shouldNotContain = self::SHOULD_NOT_CONTAIN_STRINGS;
	public $dbInput = 'string,255:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'user_error_message';
	public $example = '<h5>Not Enough Overlapping Data</h5><h5>Solution: Start Tracking</h5>';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ERROR;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::EMAIL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 500;
	public $name = self::NAME;
	public const NAME = 'user_error_message';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:500';
	public $title = 'User Error Message';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:500';
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
	/**
	 * @param int $userId
	 * @param string $name
	 * @param $meta
	 * @param $message
	 */
	public static function add(int $userId, string $name, $meta, $message){
    	Memory::add($userId, $name."\n".$message."\n".\App\Logging\QMLog::print_r($meta, true), self::NAME);
	}
	/**
	 * @param int $userId
	 * @return QMLog[]
	 */
	public static function all(int $userId): array {
		return Memory::get($userId,self::NAME) ?? [];
	}
}
