<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Astral\BaseAstralAstralResource;
use App\Fields\Field;
use App\Fields\HasMany;
use App\Fields\Text;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\UI\HtmlHelper;
trait IsNumberOfRelated {
	use IsCalculated;
	use IsInt;
	protected static function getRelatedTable(): string{
		$table = str_replace(HasMany::$number_of_, '', static::NAME);
		$table = QMStr::before("_where_", $table, $table);
        $tables = BaseModel::getAllTables();
		if(!in_array($table, $tables)){
			le("Please define getRelationshipClass and getRelatedTable for " . static::class);
		}
		if(!$table){
			le("Please define getRelationshipClass and getRelatedTable for " . static::class);
		}
		return $table;
	}
	protected static function getRelationshipName(): string{
		return str_replace(HasMany::$number_of_, '', static::NAME);
	}
	/**
	 * @return BaseModel
	 */
	protected static function getRelationshipClass(): string{
		return QMStr::tableToFullClassName(static::getRelatedTable());
	}
	/**
	 * @param string $relationshipMethod
	 * @return HasMany
	 */
	protected function getHasManyField(string $relationshipMethod): HasMany{
		$class = static::getAstralRelatedResourceClass();
		return $class::hasMany($this->getTitleAttribute(), $relationshipMethod);
	}
	/**
	 * @return BaseAstralAstralResource|string
	 */
	protected static function getAstralRelatedResourceClass(): string{
		$class = static::getRelationshipClass();
		if(!class_exists($class)){
			le("$class does not exist");
		}
		return $class::getAstralResourceClass();
	}
	protected static function getRelatedTitle(): string{
		$class = static::getRelationshipClass();
		return $class::getClassNameTitlePlural();
	}
	public static function getForeignKey(): string{
		$name = static::NAME;
		if(strpos($name, "_where_") !== false){
			return QMStr::after("_where_", $name) . "_id";
		}
		//if(strpos($name, "ct_") === 0){return str_replace("ct_", "", $name);}
		return QMStr::snakize(static::getParentShortClassName()) . "_id";
	}
	protected static function getLocalKey(): string{
		$m = (new static())->getParentModel();
		return $m->getPrimaryKey();
	}
	/**
	 * @param BaseModel $model
	 * @return int
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function calculate($model){
		$relatedClass = static::getRelationshipClass();
		$foreignKey = static::getForeignKey();
		$localKey = static::getLocalKey();
		try {
			$rel = $model->hasMany($relatedClass, $foreignKey, $localKey);
		} catch (\Throwable $e) {
			debugger($e);
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$rel = $model->hasMany($relatedClass, $foreignKey, $localKey);
		}
		$val = $rel->count();
		$model->setAttribute(static::NAME, $val);
		return $val;
	}
	public static function updateAll(){
		$idField = static::getForeignKey();
		$rel = static::getRelatedTable();
		$prop = new static();
		$tableToUpdate = $prop->table;
		$fieldToUpdate = $prop->name;
		Writable::statementStatic("
            update $tableToUpdate
                join (
                    SELECT
                        $rel.$idField,
                        COUNT($rel.id) AS total
                    FROM $rel
                    GROUP BY $rel.$idField
                ) as sel
                on sel.$idField = $tableToUpdate.id
                set $tableToUpdate.$fieldToUpdate = sel.total
        ");
	}
	public function getHardCodedValue(): ?int{
		return $this->getDBValue();
	}
	public function showOnIndex(): bool{ return false; }
	public function showOnDetail(): bool{ return true; }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Text
	 */
	public function getIntLinkToIndexField($resolveCallback = null, string $name = null): Text{
		return Text::make($this->getTitleAttribute(), $this->name ?? $name, $resolveCallback ?? function(){
				$val = $this->getDBValue();
				if(!$val){
					return null;
				}
				$model = $this->getParentModel();
				$class = static::getAstralRelatedResourceClass();
				if(!class_exists($class)){  // I haven't created resources for Applications class yet for instance
					QMLog::error("Resource class $class does not exist!");
					return $val;
				}
				$url = $class::getDataLabIndexUrl([static::getForeignKey() => $model->getId()]);
				return HtmlHelper::getTailwindLink($url, $val, "See " . static::getRelatedTitle(), '_self');
			})->asHtml()->sortable();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getIntLinkToIndexField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getIntLinkToIndexField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getIntLinkToIndexField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getIntLinkToIndexField($name, $resolveCallback);
	}
}
