<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\BadRequestException;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\IsInt;
use App\Types\QMArr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use Doctrine\DBAL\Types\Types;
use App\Fields\Field;
use OpenApi\Generator;
class BaseIntegerIdProperty extends BaseProperty{
	use IsInt;
	public $autoIncrement = true;
	public $dbInput = 'integer,true,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Unique identifier';
	public $example = null; // Keep this null!
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlType = 'text';
	public $image = ImageUrls::FACTORS_SLIDE;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'id';
	public $canBeChangedToNull = false;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|numeric|min:1';
	public $title = 'ID';
    public $type = self::TYPE_INTEGER;
	public const NAME_SYNONYMS = [];
    public function isId():bool{return true;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getIdField($name, $resolveCallback);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getIdField($name, $resolveCallback);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
        return $this->getIdField($name, $resolveCallback);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getIdField($name, $resolveCallback);
    }
	/**
	 * @param $data
	 * @return int|null|string
	 */
	public static function pluckByName($data){
        if($name = static::pluckName($data, false)){
            return static::fromName($name);
        }
        return null;
    }
    /**
     * @param BaseModel|array|object|DBModel|string $data
     * @return int|string|null
     */
    public static function pluck($data){
        if(is_int($data)){
            return $data;
        }
	    if(is_string($data)){
		    return static::fromName($data);
	    }
        return parent::pluck($data);
    }
	public static function getSynonyms(): array {
		$synonyms = static::SYNONYMS;
		$synonyms[] = static::NAME; // Make sure id's last so we defer to 'variable_id' instead of id when plucking
		// from user variables
		return array_unique($synonyms);
	}
	/**
	 * @param $data
	 * @return int|mixed|null|string
	 */
	public static function pluckOrDefault($data){
        $val = static::pluck($data);
        if($val === null){
            $val = static::pluckByName($data);
        }
        if($val === null){
            $val = static::getDefault($data);
        }
        return $val;
    }
    /**
     * @param $data
     * @return BaseModel|null
     */
    public static function pluckParentModel($data):?BaseModel{
        if($id = static::pluckOrDefault($data)){
            return static::findParent($id);
        }
        return null;
    }
    /**
     * @param $data
     * @return BaseModel|null
     */
    public static function pluckParentDBModel($data):?DBModel{
        $id = static::pluckOrDefault($data);
        if($id){return static::findParentDBModel($id);}
        return null;
    }
    protected static function getNameSynonyms(): array {
        $arr = static::NAME_SYNONYMS ?? [];
        $arr[] = str_replace("_id", "_name", static::NAME);
        return array_unique($arr);
    }
    /**
     * @param BaseModel|array|object|DBModel|string|int $data
     * @param bool $fallback
     * @return mixed|null
     */
    public static function pluckName($data, bool $fallback = false){
        $synonyms = static::getNameSynonyms();
        if(!$synonyms){return null;}
        $val = QMArr::getValue($data, $synonyms);
        if($val == null && $fallback){return static::getDefault($data);}
        if(is_object($val)){
            return $val->name;
        }
        if($val && AppMode::isApiRequest()){$val = str_replace("+", " ", $val);}
        return $val;
    }
    /**
     * @param string $name
     * @return int|string|null
     */
    public static function fromName(string $name){
        $model = static::findByName($name);
        if($model){return $model->getId();}
        return null;
    }
    /**
     * @param string $name
     * @return BaseModel|DBModel
     */
    public static function findByName(string $name) {
        return null; // Implement in children
    }
    /**
     * @param $data
     * @return string|int|null
     */
    public static function pluckNameOrId($data){
	    if(!$data){return null;}
        $nameOrId = static::pluckNameOrIdDirectly($data);
        if($nameOrId){return $nameOrId;}
        return static::pluckOrDefault($data);
    }
    /**
     * @param $data
     * @return string|int|null
     */
    public static function pluckNameOrIdDirectly($data){
	    if(!$data){le("No Data provided to ".__METHOD__);}
        $id = static::pluck($data);
        if($id){return $id;}
        $name = static::pluckName($data);
        if($name){return $name;}
        return null;
    }
    public static function throwMissingNameException(): void{
        throw new BadRequestException("Please provide ".static::NAME_SYNONYMS[0]." in request");
    }
    /**
     * @param bool $throwException
     * @return array|mixed|null|string
     */
    public static function nameFromRequest(bool $throwException = false){
        $params = qm_request()->input() + qm_request()->query();
        $name = static::pluckName($params);
        if(empty($name) && $throwException){static::throwMissingNameException();}
        return $name;
    }
    /**
     * @param bool $throwException
     * @return int|null|string
     */
    public static function nameOrIdFromRequest(bool $throwException = false){
		$get = QMRequest::getInput();
        $nameOrId = static::pluckNameOrId($get);
		if(!$nameOrId){
			$body = QMRequest::body();
			if($body){$nameOrId = static::pluckNameOrId($body);}
		}
        if(!$nameOrId && $throwException){static::throwMissingParameterException();}
        return $nameOrId;
    }
    /**
     * @param $data
     * @return BaseModel|null
     */
    public static function parentModelFromDataOrRequest($data): ?BaseModel {
        $id = static::fromDataOrRequest($data);
        if(!$id){return null;}
        return static::findParent($id);
    }

}
