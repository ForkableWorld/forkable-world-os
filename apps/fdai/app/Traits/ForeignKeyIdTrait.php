<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\BaseModel;
use App\Astral\BaseAstralAstralResource;
use App\Fields\BelongsTo;
use App\Fields\Field;
use App\Fields\Text;
trait ForeignKeyIdTrait {
	public function showOnIndex(): bool{ return false; }
	public function showOnDetail(): bool{ return true; }
	public function showOnUpdate(): bool{ return false; }
	public function showOnCreate(): bool{ return false; }
	public function isId(): bool{ return true; }
	/**
	 * @return int|null|string
	 */
	public function getExample(){
		/** @var BaseModel $parent */
		if($parent = $this->parentModel){
			$val = $parent->getAttribute($this->name);
			if($val !== null){
				return $val;
			}
		}
		return $this->example;
	}
	public function getMaximum(): ?float{
		//if($this->maximum !== null){le('$this->maximum !== null');}
		return $this->maximum;
	}
	public function getMinimum(): ?float{
		if($this->isString()){
			return null;
		}
		//if($this->minimum !== null){le("Don't use a minimum for foreign keys because it breaks tests and w");}
		return $this->minimum = 1; // Lets keep this to avoid accidentally saving stuff with 0 id's
	}
	/**
	 * @return BaseModel
	 */
	abstract public static function getForeignClass(): string;
	/** @noinspection PhpUnused */
	public function isGeneratedByDB(): bool{
		return false;
	}
	/**
	 * @param $data
	 * @return BaseModel
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findRelated($data){
		$id = static::pluckOrDefault($data);
		$class = self::getForeignClass();
		return $class::findInMemoryOrDB($id);
	}
	/**
	 * @param $data
	 * @return BaseModel
	 */
	public static function pluckRelated($data): BaseModel{
		return static::findRelated($data);
	}
	/**
	 * @return BaseModel
	 */
	public static function getRelatedClass(): string{
		return self::getForeignClass();
	}
	/**
	 * @param string|null $name
	 * @return BelongsTo
	 */
	protected function getBelongsToField(?string $name): BelongsTo{
		$resource = static::resourceClass();
		$title = $name ?? $this->title;
		$field = $resource::belongsTo($title, $this->relationshipMethod())
			->hideFromIndex();
		return $field;
	}
	public static function belongsTo(string $name = null): BelongsTo{
		$p = new static();
		return $p->getBelongsToField($name);
	}
	public function relationshipMethod(): string{
		return str_replace("_id", "", $this->name);
	}
	/**
	 * @return string|BaseAstralAstralResource
	 */
	public static function resourceClass(): string{
		$foreign = self::getForeignClass();
		/** @var BaseAstralAstralResource $resource */
		$resource = BaseAstralAstralResource::toResourceClassName($foreign);
		return $resource;
	}
	public function getRelated(): BaseModel{
		$parent = $this->getParentModel();
		$model = $parent->getRelation($this->relationshipMethod());
		return $model->getTitleAttribute();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getBelongsToField($name);
		return Text::make($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				$attribute = $this->name;
				/** @var BaseModel $resource */
				$id = $resource->getAttribute($attribute);
				if(!$id){
					return "No id provided to Text callback";
				}
				$model = static::findRelated($id);
				return $model->getTitleAttribute();
			})->sortable()->readonly()->detailLink();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		$field = $this->getIndexField($resolveCallback, $name);
		return $field;
		$field = $this->getBelongsToField($name);
		return $field->withoutTrashed()->searchable();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getBelongsToField($name)->withoutTrashed()->searchable();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getBelongsToField($name)->withoutTrashed()->searchable();
	}
}
