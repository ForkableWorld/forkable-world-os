<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Models\WpTerm;
use App\Models\WpTermmetum;
use App\Models\WpUsermetum;
use Corcel\Model\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use UnexpectedValueException;
/** Trait HasMetaFields
 * @package Corcel\Traits
 * @author Junior Grossi <juniorgro@gmail.com>
 */
trait MetaFieldsTrait {
	/**
	 * @var array
	 */
	protected $builtInClasses = [
		WpPost::class => WpPostmetum::class,
		WpTerm::class => WpTermmetum::class,
		User::class => WpUsermetum::class,
	];
	/**
	 * @return HasMany
	 */
	public function fields(): HasMany{
		return $this->meta();
	}
	/**
	 * @return HasMany
	 */
	public function meta(): HasMany{
		return $this->hasMany($this->getMetaClass(), $this->getMetaForeignKey());
	}
	/**
	 * @return string
	 * @throws UnexpectedValueException
	 */
	protected function getMetaClass(): string{
		foreach($this->builtInClasses as $model => $meta){
			if($this instanceof $model){
				return $meta;
			}
		}
		throw new UnexpectedValueException(sprintf('%s must extends one of Corcel built-in models: Comment, Post, Term or User.',
			static::class));
	}
	/**
	 * @return string
	 * @throws UnexpectedValueException
	 */
	protected function getMetaForeignKey(): string{
		foreach($this->builtInClasses as $model => $meta){
			if($this instanceof $model){
				$key = sprintf('%s_id', strtolower(class_basename($model)));
				/** @noinspection SpellCheckingInspection */
				if($key === "wppost_id"){
					return WpPostmetum::FIELD_POST_ID;
				}
				return $key;
			}
		}
		throw new UnexpectedValueException(sprintf('%s must extends one of Corcel built-in models: Comment, Post, Term or User.',
			static::class));
	}
	/**
	 * @param Builder $query
	 * @param string|array $meta
	 * @param mixed $value
	 * @param string $operator
	 * @return Builder
	 */
	public function scopeHasMeta(Builder $query, $meta, $value = null, string $operator = '='): Builder{
		if(!is_array($meta)){
			$meta = [$meta => $value];
		}
		foreach($meta as $key => $value){
			$query->whereHas('meta', function(Builder $query) use ($key, $value, $operator){
				if(!is_string($key)){
					return $query->where('meta_key', $operator, $value);
				}
				$query->where('meta_key', $operator, $key);
				return is_null($value) ? $query : $query->where('meta_value', $operator, $value);
			});
		}
		return $query;
	}
	/**
	 * @param Builder $query
	 * @param string $meta
	 * @param mixed $value
	 * @return Builder
	 */
	public function scopeHasMetaLike(Builder $query, string $meta, $value = null): Builder{
		return $this->scopeHasMeta($query, $meta, $value, \App\Storage\DB\ReadonlyDB::like());
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function saveField(string $key, $value): bool{
		return $this->saveMeta($key, $value);
	}
	/**
	 * @param string|array $key
	 * @param mixed $value
	 * @return bool
	 */
	public function saveMeta($key, $value = null): bool{
		if(is_array($key)){
			foreach($key as $k => $v){
				$this->saveOneMeta($k, $v);
			}
			$this->load('meta');
			return true;
		}
		return $this->saveOneMeta($key, $value);
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	private function saveOneMeta(string $key, $value): bool{
		$meta = $this->meta()->where('meta_key', $key)->firstOrNew(['meta_key' => $key]);
		$result = $meta->fill(['meta_value' => $value])->save();
		$this->load('meta');
		return $result;
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return Model
	 */
	public function createField(string $key, $value): Model{
		return $this->createMeta($key, $value);
	}
	/**
	 * @param string|array $key
	 * @param mixed $value
	 * @return Model|Collection
	 */
	public function createMeta($key, $value = null){
		if(is_array($key)){
			return collect($key)->map(function($value, $key){
				return $this->createOneMeta($key, $value);
			});
		}
		return $this->createOneMeta($key, $value);
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return Model
	 */
	private function createOneMeta(string $key, $value): Model{
		$meta = $this->meta()->create([
			'meta_key' => $key,
			'meta_value' => $value,
		]);
		$this->load('meta');
		return $meta;
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 */
	public function getMeta(string $attribute){
		$meta = $this->meta;
		if($value = $meta->{$attribute}){
			return $value;
		}
		return null;
	}
}
