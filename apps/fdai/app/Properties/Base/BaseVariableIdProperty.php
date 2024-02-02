<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Slim\Model\DBModel;
use App\Traits\ForeignKeyIdTrait;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseVariableIdProperty extends BaseIntegerIdProperty{
    use ForeignKeyIdTrait;
    /**
     * @var int
     * The smaller the number, the higher the placement in a form
     * Make sure it's 2 digits i.e. "01" as opposed to "1"
     */
    public $order = "00";
	public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
    public $description = 'What factor or outcome are you interested in?';
	public $example = OverallMoodCommonVariable::ID;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'variable_id';
	public $canBeChangedToNull = false;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
	public const SYNONYMS = [
	    'variable_id',
    ];
	public const NAME_SYNONYMS = [
        'variable_name',
        'name',
        'variable',
    ];
	/**
	 * @param BaseModel|DBModel|array|object $data
	 * @return int|null
	 */
	public static function pluck($data): ?int{
        if(is_int($data)){return $data;}
        if(is_string($data)){return static::fromName($data);}
        return parent::pluck($data);
    }
	/**
	 * @param null $data
	 * @return int|null
	 */
	public static function getDefault($data = null): ?int{
        $name = static::pluckName($data);
        if(!$name){return null;}
        return self::fromNameOrNew($name, $data);
    }
    /**
     * @param string $name
     * @param array|null $newVariableData
     * @return int|null
     */
    public static function fromNameOrNew(string $name, ?array $newVariableData = []): int{
        return VariableIdProperty::fromNameOrNew($name, $newVariableData);
    }
    /**
     * Returns the variable ID for given original name from the variables table.
     * This first checks the users cached variables to avoid an unnecessary call
     * to the database.
     * @param string $name
     * @return int|null
     */
    public static function fromName(string $name): ?int {
        return VariableIdProperty::fromName($name);
    }
    /**
     * @return Variable
     */
    public static function getForeignClass(): string{
        return Variable::class;
    }
    /**
     * @param $data
     * @return Variable|null
     */
    public static function pluckParentModel($data): ?BaseModel{
        $id = static::pluck($data);
        if($variable = parent::pluckParentModel($data)){
            return $variable;
        }
        if($name = static::pluckName($data)){
            return Variable::findByNameLikeOrId($name);
        }
		if($id){le('$id');}
        return null;
    }
    public function getVariableName(): ?string{
        return VariableNameProperty::fromId($this->getDBValue());
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
