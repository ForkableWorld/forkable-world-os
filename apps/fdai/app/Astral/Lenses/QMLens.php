<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Http\Urls\AstralUrl;
use App\Models\BaseModel;
use App\Astral\BaseAstralAstralResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\AstralRequest;
use App\Lenses\Lens;
abstract class QMLens extends Lens {
	/**
	 * Create a new lens instance.
	 * @param Model|null $resource
	 * @return void
	 */
	public function __construct($resource = null){
		parent::__construct($resource);
	}
	/**
	 * Get the displayable name of the metric.
	 * @return string
	 */
	public function name(): string{
		$name = parent::name();
		$name = str_replace(" Lens", "", $name);
		return $this->name ?: $name;
	}
	/**
	 * Get the URI key for the lens.
	 * @return string
	 */
	public function uriKey(): string{
		return Str::slug($this->name());
	}
	public function getTable(): string{
		$resourceClass = $this->getModelInstance();
		return $resourceClass->getTable();
	}
	public function getModelInstance(): BaseModel{
		$resource = $this->getResource();
		return $resource->getModelInstance();
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function fields(Request $request){
		$res = $request->newResourceWith($this->resource);
		$fields = $res->fields($request);
		return $fields;
	}
	/**
	 * Get the filters available for the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function filters(Request $request){
		$res = $request->newResourceWith($this->getModel());
		$filters = $res->filters($request);
		return $filters;
	}
	/**
	 * @return BaseAstralAstralResource
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getResource(){
		/** @var BaseAstralAstralResource $resource */
		$resource = $this->resource;
		return $resource;
	}
	public function getResourceKey(): string{
		return $this->getResource()->getRouteKey();
	}
	public function getModel(): BaseModel{
		return $this->getResource()->getModel();
	}
	/**
	 * @return string|BaseAstralAstralResource
	 */
	public function getResourceClass(): string{
		return get_class($this->getResource());
	}
	public function getUrl(): string{
		return AstralUrl::getAstralUrl("{$this->getResourceKey()}/lens/" . $this->uriKey());
	}
	public static function url(): string{
		return (new static())->getUrl();
	}
}
