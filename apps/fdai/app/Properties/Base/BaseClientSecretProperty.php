<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\DataSources\QMClient;
use App\Properties\BaseProperty;
use App\Traits\HasHeaderValue;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseClientSecretProperty extends BaseProperty{
	use IsString, HasHeaderValue;
	public const TEST_CLIENT_SECRET = 'oauth_test_secret';
	public const HEADER_NAME = 'X-Client-Secret';
	public $dbInput = 'string,80';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'client_secret';
	public $example = 'iAj3y25FkYBH68H1tYPGvnAdCSUn8kiY';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 80;
	public $name = self::NAME;
	public const NAME = 'client_secret';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:80';
	public $title = 'Client Secret';
	public $type = PhpTypes::STRING;
	public $validations = 'required';
	const SYNONYMS = [
		'quantimodo_client_secret',
		QMClient::FIELD_CLIENT_SECRET
	];
	protected static function getHeaderNames(): array{
		return [self::HEADER_NAME];
	}
}
