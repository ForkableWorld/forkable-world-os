<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariable;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Variables\QMUserVariable;
class BaseUserVariableIdProperty extends BaseIntegerIdProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The associated user variable statistics and settings object';
	public $example = null;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'user_variable_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'User Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public static function getDefault($data = null){
        $userId = BaseUserIdProperty::pluckOrDefault($data);
        $variableId = BaseVariableIdProperty::pluckOrDefault($data);
        if(!$variableId || !$userId){
            return null;
        }
        $fromMem = UserVariable::getFromMemoryWhere([
            UserVariable::FIELD_VARIABLE_ID => $variableId,
            UserVariable::FIELD_USER_ID => $userId
        ]);
        if($fromMem){
            return $fromMem[0]->getUserVariableId();
        }
        $uv = UserVariable::whereUserId($userId)
            ->where(UserVariable::FIELD_VARIABLE_ID, $variableId)
            ->first();
        if($uv){
			$uv->addToMemory();
			return $uv->id;
		}
        $uv = UserVariable::findOrCreateByNameOrId($userId,$variableId);
        return $uv->getUserVariableId();
    }
    /**
     * @return string
     */
    public static function getForeignClass(): string{
        return UserVariable::class;
    }
	public static function populateIfEmpty(array $array): array {
		if (array_key_exists(static::NAME, $array) && empty($array[static::NAME])) {
			$variableKey = str_replace('user_variable', "variable", static::NAME);
			$variableId = $array[$variableKey];
			$userId = $array[BaseUserIdProperty::NAME];
			$userVariable = UserVariable::findOrCreateByNameOrId($userId, $variableId);
			$array[static::NAME] = $userVariable->id;
		}
		return $array;
	}
}
