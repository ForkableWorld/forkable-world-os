<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotFoundException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Variable;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMCommonVariable;
class VariableIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'variable_id',
        'id',
    ];
    public const NAME_SYNONYMS = [
        'variable_name',
        'name',
    ];
	/**
	 * @param $data
	 * @return int|null
	 */
	public static function pluckOrDefault($data): ?int {
        if(is_string($data)){
            if(is_numeric($data)){return $data;}
            return static::fromName($data);
        }
        if(is_int($data)){
            return $data;
        }
        return parent::pluckOrDefault($data);
    }
    /**
     * Returns the variable ID for given original name from the variables table.
     * This first checks the users cached variables to avoid an unnecessary call
     * to the database.
     * @param string $name
     * @return int|null
     */
    public static function fromName(string $name): ?int{
        if(strpos($name, "%") !== false){
            return null;
        }
        if ($variableId = Memory::get($name, __FUNCTION__)) {
            return $variableId;
        }
        // uses lots of memory for some reason
        //$constants = QMCommonVariable::getHardCodedVariables();
        //$match = Arr::first($constants, static function ($v) use ($name) {return $v->name === $name;});
        //if ($match) {$variableId = $match->id;}
	    if($cv = Variable::findByName($name)){
	        $variableId = $cv->getVariableIdAttribute();
	    }
	    Memory::set($name, $variableId, __FUNCTION__);
        return $variableId;
    }
    /**
     * @param string $name
     * @param array|null $newVariableData
     * @return int|null
     */
    public static function fromNameOrNew(string $name, array $newVariableData = null): int{
		if(!$name){le('!$name');}
        $id = VariableIdProperty::fromName($name);
        if(!$id){
            $cv = QMCommonVariable::findOrCreateByName($name, $newVariableData);
            $id = $cv->getVariableIdAttribute();
        }
		if(!$id){le('!$id');}
        return $id;
    }
    /**
     * @param mixed $data
     * @return int
     */
    public static function pluck($data): ?int{
        if(is_int($data)){return $data;}
        return parent::pluck($data);
    }
	/**
	 * @param int $replacementVariableId
	 * @param int $variableToDeleteId
	 * @param string $reason
	 * @return bool
	 */
    public static function replaceEverywhere(int $replacementVariableId, int $variableToDeleteId, string $reason): bool{
        $replacementVariable = QMCommonVariable::find($replacementVariableId);
        $variableToDelete = QMCommonVariable::find($variableToDeleteId);
        QMLog::error("Replacing $variableToDelete->name with $replacementVariable->name because $reason");
        $replacementUnit = $replacementVariable->getUserOrCommonUnit();
        $deletedUnit = $variableToDelete->getUserOrCommonUnit();
        if($replacementUnit->id !== $deletedUnit->id){
            QMLog::error('Cannot replace '.
                $variableToDelete->name.
                " (".
                $deletedUnit->abbreviatedName.
                ") with ".
                $replacementVariable->name.
                " (".
                $replacementUnit->abbreviatedName.
                ') because unit ids do not match!');
            return false;
        }
        $relations = QMCommonVariable::getRelatedDbFields();
        foreach($relations as $relation){
            $table = $relation['table'];
            $field = $relation['field'];
            $qb = Writable::getBuilderByTable($table);
            $qb->where($field, $variableToDelete->getId());
            Writable::statementStatic("update ignore $table set $field = $replacementVariableId where $field = $variableToDeleteId");
            if($table === Measurement::TABLE){
                Writable::statementStatic("update measurements m
                    join user_variables uv on m.user_id = uv.user_id and m.variable_id = uv.variable_id
                    set m.user_variable_id = uv.id
                    where m.user_variable_id <> uv.id and m.variable_id = $replacementVariableId
                ");
            }
            if($table === UserVariableRelationship::TABLE){
                Writable::statementStatic("update user_variable_relationships c
                    join user_variables uv on c.user_id = uv.user_id and c.cause_variable_id = uv.variable_id
                    set c.cause_user_variable_id = uv.id
                    where c.cause_user_variable_id <> uv.id and c.cause_variable_id = $replacementVariableId
                ");
                Writable::statementStatic("update user_variable_relationships c
                    join user_variables uv on c.user_id = uv.user_id and c.effect_variable_id = uv.variable_id
                    set c.effect_user_variable_id = uv.id
                    where c.effect_user_variable_id <> uv.id and c.effect_variable_id = $replacementVariableId
                ");
            }
            Writable::statementStatic("delete $table from $table where $field = $variableToDeleteId");
        }
        /** @noinspection PhpUnusedLocalVariableInspection */
        $result =
            Variable::query()
                ->where(Variable::FIELD_ID, $variableToDelete->getId())
                ->update([Variable::FIELD_DELETED_AT => now_at()]);
        try {
            $replacementVariable->analyzeSourceObjects("Renamed variable");
        } catch (AlreadyAnalyzingException $e) {
            le($e);
        } catch (AlreadyAnalyzedException | TooSlowToAnalyzeException | StupidVariableNameException | NotEnoughDataException | ModelValidationException | DuplicateFailedAnalysisException $e) {
        }
	    return true;
    }
	/**
	 * @param string $nameOrSynonym
	 * @return int
	 */
    public static function getIdByNameOrSynonym(string $nameOrSynonym): int{
        $variable = QMCommonVariable::findByNameIdOrSynonym($nameOrSynonym);
        if($variable){
            return $variable->getVariableIdAttribute();
        }
        $variableRow = QMCommonVariable::readonly()->where('name', $nameOrSynonym)->first();
        if($variableRow){
            return $variableRow->id;
        }
        $variableRow =
            Variable::query()->whereLike(Variable::FIELD_SYNONYMS, '%'.$nameOrSynonym.'%')
                ->first();
        if($variableRow){
            return $variableRow->id;
        }
        throw new NotFoundException("Variable $nameOrSynonym not found!");
    }
}
