<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\GlobalVariableRelationship;
use App\Models\Study;
use App\Properties\Base\BaseClientIdProperty;
use App\Traits\PropertyTraits\IsString;
use App\Traits\PropertyTraits\StudyProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use OpenApi\Generator;
class StudyClientIdProperty extends BaseClientIdProperty
{
    use StudyProperty, IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = GlobalVariableRelationship::FIELD_CLIENT_ID;
	public $example = self::CLIENT_ID_OAUTH_TEST_CLIENT;
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 80;
	public $minLength = 2;
	public const NAME = Study::FIELD_CLIENT_ID;
	public $name = self::NAME;
	public $canBeChangedToNull = false;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:80';
	public $title = 'Client';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:80';
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    public const SYNONYMS = [
        'study_id',
        'study_client_id'
    ];
	public static function fromRequest(bool $throwException = false): ?string{
		return self::fromRequestDirectly($throwException);
	}
	public static function getSynonyms(): array{
		return self::SYNONYMS;
	}
	/**
	 * @param bool $throwException
	 * @return mixed|null
	 */
	public static function fromRequestDirectly(bool $throwException = false): ?string {
		if(!AppMode::isApiRequest() && !$throwException){return null;}
		if($data = qm_request()->input() + qm_request()->query()){
			$val = static::pluck($data);
		} else {
			$val = null;
		}
		if($val === null && $throwException){static::throwMissingParameterException();}
		return $val;
	}
}
