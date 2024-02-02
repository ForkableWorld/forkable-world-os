<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\BadRequestException;
use App\Models\BaseModel;
use App\Models\Unit;
use App\Properties\Unit\UnitNameProperty;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\HasFilter;
use App\Traits\HasModel\HasUnit;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Types\QMArr;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Units\OneToFiveRatingUnit;
use App\Fields\Field;
use OpenApi\Generator;
class BaseUnitIdProperty extends BaseIntegerIdProperty{
    use HasUnit, ForeignKeyIdTrait, HasFilter;
	public $dbInput = 'smallInteger,false,true';
	public $dbType = 'smallint';
	public $default = Generator::UNDEFINED;
	public $description = 'The default unit for the variable';
	public $example = OneToFiveRatingUnit::ID;
	public $fieldType = 'smallInteger';
	public $fontAwesome = FontAwesome::UNIT;
	public $htmlType = 'text';
	public $image = ImageUrls::UNIT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'unit_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:65535';
	public $title = 'Unit';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public const SYNONYMS = [
        'unit_id',
    ];
	public const NAME_SYNONYMS = [
        'unit_name',
        'unit_abbreviated_name',
        'abbreviated_unit_name',
        'unit',
    ];
    public function __construct(BaseModel $parentModel = null){
        parent::__construct($parentModel);
    }
	/**
	 * @param $data
	 * @return int|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function pluckOrDefault($data){
        $ids = $names = [];
        $id = parent::pluck($data);
        if($id){$ids[] = $id;}
        foreach(static::NAME_SYNONYMS as $synonym){
            if($name = QMArr::pluckValue($data, $synonym)){
                if(!is_string($name)){
                    $name = UnitNameProperty::pluck($name);
                    if(!$name){continue;}
                }
                $names[] = $name;
            }
        }
        foreach($names as $name){
            if($name instanceof QMUnit){
                $ids[] = $name->id;
            } else {
                $ids[] = QMUnit::find($name)->id;
            }
        }
        $ids = array_values(array_unique($ids));
        if(count($ids) > 1){
            $fromIds = [];
            foreach($ids as $id){
                $fromIds[] = QMUnit::find($id)->name;
            }
            throw new BadRequestException("Please only submit one of unit_id or unit_name.  ".
                "\n\tYou submitted both ".implode(" and ", $fromIds));
        }
        return $ids[0] ?? null;
    }
    /**
     * @return Unit
     */
    public static function getForeignClass(): string{
        return Unit::class;
    }
    /**
     * @param $data
     * @return null|QMUnit
     */
    public static function pluckParentDBModel($data): ?DBModel {
        if($id = static::pluckOrDefault($data)){
            return QMUnit::getByNameOrId($id);
        }
        return null;
    }
    /**
     * @param string $name
     * @return QMUnit
     */
    public static function findByName(string $name): QMUnit{
        return QMUnit::findByNameOrSynonym($name);
    }
    /**
     * @param $data
     * @return Unit
     */
    public static function pluckParentModel($data): ?BaseModel{
        return parent::pluckParentModel($data);
    }
    public function getOptions(): array{
        $unit = $this->getUnit();
        if(!$unit){return QMUnit::allOptions();}
        return $unit->getCompatibleOptions();
    }
    public function getUnit(): Unit {
        $id = $this->getDBValue();
        return Unit::findInMemoryOrDB($id);
    }
    public function getHardCodedValue(): ?string{
        $val = $this->getDBValue();
        if(!$val){return null;}
        $unit = QMUnit::find($val);
        return get_class($unit)."::ID";
    }
    public function getUnitIdAttribute(): ?int{
        return $this->getDBValue();
    }
    public function getFilterOptions(): array{
        return QMUnit::getOptionsByName();
    }
    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function applyFilter($query, $type){
        if($type){
            $query->where($this->table.'.'.$this->name, $type);
        }
        return $query;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		$f = $this->getBelongsToField($name)->searchable(false);
		$f->displaysWithTrashed = false;
		return $f;
	}
}
