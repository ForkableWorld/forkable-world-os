<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Astral\VariableCategoryBaseAstralResource;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\HasFilter;
use App\Traits\HasModel\HasVariableCategory;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use KirschbaumDevelopment\Astral\InlineSelect;
use App\Fields\Field;
use App\Fields\Select;
use App\Http\Requests\AstralRequest;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use OpenApi\Generator;
class BaseVariableCategoryIdProperty extends BaseIntegerIdProperty{
    use ForeignKeyIdTrait, HasFilter;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = Generator::UNDEFINED;
	public $description = 'The variable category is indicative of the type of variable.';
	public $example = EmotionsVariableCategory::ID;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'variable_category_id';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'nullable|integer|min:1|max:300';
	public $title = 'Variable Category';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = false;
	public $validations = 'nullable|integer|min:1|max:300';
    public const SYNONYMS = [
        'variable_category_id',
        'category_id',
    ];
	public const NAME_SYNONYMS = [
        'variable_category_name',
        'category_name',
        'variable_category',
        'category',
    ];
    /**
     * @param DBModel|BaseModel|HasVariableCategory|QMUserVariable $model
     * @return int|mixed|string|null
     */
    public static function calculate($model){
        $id = static::pluck($model);
        if(!$id){
            $name = $model->variableCategoryName ?? null;
            if($name){
                $id = QMVariableCategory::findByNameOrSynonym($name)->getId();
            }
        }
        if(!$id){
            /** @var Variable $l */
            $l = $model->getRelation('variable');
            $id = $l->variable_category_id;
        }
        return $id;
    }
    public function getExample(){
        return $this->example = EmotionsVariableCategory::ID;
    }
    /**
     * @return VariableCategory
     */
    public static function getForeignClass(): string{
        return VariableCategory::class;
    }
    public static function getSynonyms(): array{
        return parent::getSynonyms();
    }
    /**
     * @param string $name
     * @return QMVariableCategory
     */
    public static function findByName(string $name): ?QMVariableCategory{
        return QMVariableCategory::findByNameOrSynonym($name);
    }
    public function validate(): void {
        parent::validate();
    }
    public function cannotBeChangedToNull(): bool{
        return parent::cannotBeChangedToNull();
    }
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        if(QMAuth::isAdmin()){
            return $this->getInlineSelectField();
        }
        return VariableCategoryBaseAstralResource::belongsTo($name ?? $this->title,
            str_replace("_id", "", $this->name))
            ->withoutTrashed();
    }
    public function getFilterOptions(): array{
        return QMVariableCategory::getOptionsByName();
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
    public function getHardCodedValue(): ?string{
        $val = $this->getDBValue();
        if(!$val){return null;}
        $unit = QMVariableCategory::find($val);
        return get_class($unit)."::ID";
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    protected function assertNotBoring(){
        $id = $this->getDBValue();
        if(in_array($id, QMVariableCategory::getBoringVariableCategoryIds())){
            $this->throwException("this category is boring");
        }
    }
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return \App\Fields\Field
     */
    public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getVariableCategorySelector();
    }
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return \App\Fields\Field
     */
    public function getCreateField($resolveCallback = null, string $name = null): Field{
        return $this->getVariableCategorySelector();
    }
    /**
     * @return Select
     */
    protected function getVariableCategorySelector(): Select{
        return Select::make('Category', $this->name)->options(function(){
            return QMVariableCategory::getOptionsById();
        })->displayUsingLabels();
    }
    /**
     * @return InlineSelect
     */
    protected function getInlineSelectField(): InlineSelect{
        return InlineSelect::make('Category', $this->name)
            ->options(QMVariableCategory::getOptionsById())
            ->inlineOnIndex()
            ->inlineOnLens()
            ->disableTwoStepOnIndex()
            ->disableTwoStepOnLens()
            ->inlineOnDetail()
            ->displayUsingLabels();
    }
}
