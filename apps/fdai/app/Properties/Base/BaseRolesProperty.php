<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\UnauthorizedException;
use App\Properties\BaseProperty;
use App\Slim\Middleware\QMAuth;
use App\Traits\PropertyTraits\AdminProperty;
use App\Traits\PropertyTraits\IsArray;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseRolesProperty extends BaseProperty{
	use IsArray, AdminProperty;
	public const ROLE_ADMINISTRATOR = 'administrator';
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'An array containing all roles possessed by the user.  This indicates whether the use has roles such as administrator, developer, patient, student, researcher or physician. ';
	public $example = [self::ROLE_ADMINISTRATOR];
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ROLES;
	public $htmlInput = 'text';
	public $enum = [self::ROLE_ADMINISTRATOR];
	public $htmlType = 'text';
	public $image = ImageUrls::ROLES;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'roles';
	public $phpType = PhpTypes::ARRAY;
	public $rules = 'nullable|max:255';
	public $title = 'Roles';
	public $type = PhpTypes::ARRAY;
	public $canBeChangedToNull = false;
	public $validations = 'nullable|max:255';
	/**
	 * @throws UnauthorizedException
	 */
	public function authorizeUpdate(): void {
		QMAuth::isAdminOrException();
	}
	protected function isLowerCase(): bool{return true;}
	/**
	 * @param $value
	 * @return string
	 */
	public function processAndSetDBValue($value): string {
		return parent::processAndSetDBValue($value);
	}
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 */
	public function validate(): void {
		parent::validate();
	}
	public function getEnumOptions(): array{return $this->enum;}
}
