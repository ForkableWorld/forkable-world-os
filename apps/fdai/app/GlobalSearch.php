<?php

namespace App;

use Illuminate\Support\Collection;
use App\Fields\Avatar;
use App\Http\Requests\AstralRequest;

class GlobalSearch
{
    /**
     * The request instance.
     *
     * @var \App\Http\Requests\AstralRequest
     */
    public $request;

    /**
     * The resource class names that should be searched.
     *
     * @var \Illuminate\Support\Collection
     */
    public $resources;

    /**
     * Create a new global search instance.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Support\Collection  $resources
     * @return void
     */
    public function __construct(AstralRequest $request, Collection $resources)
    {
        $this->request = $request;
        $this->resources = $resources;
    }

    /**
     * Get the matching resources.
     *
     * @return array
     */
    public function get()
    {
        $formatted = [];

        $results = $this->getSearchResults();
        foreach ($results as $resource => $models) {
            /** @var \App\Astral\BaseAstralAstralResource $resource */
            foreach ($models as $model) {
                $instance = new $resource($model);
                /** @var Avatar $avatarField */
                $avatarField = $instance->resolveAvatarField($this->request);
                $formatted[] = [
                    'resourceName' => $resource::uriKey(),
                    'resourceTitle' => $resource::label(),
                    'title' => $instance->title(),
                    'subTitle' => $instance->subtitle(),
                    'resourceId' => $model->getKey(),
                    'url' => url(Astral::path().'/resources/'.$resource::uriKey().'/'.$model->getKey()),
                    'avatar' => ($avatarField) ? $avatarField->resolveThumbnailUrl() : null,
                    'rounded' => $avatarField && $avatarField->isRounded(),
                    'linksTo' => $instance->globalSearchLink($this->request),
                ];
            }
        }

        return $formatted;
    }

    /**
     * Get the search results for the resources.
     *
     * @return array
     */
    protected function getSearchResults()
    {
        $results = [];

        foreach ($this->resources as $resource) {
            $q = $resource::newModel()->newQuery();
            $s = $this->request->search;
            $query = $resource::buildIndexQuery($this->request, $q, $s);
            $models = $query->limit($resource::$globalSearchResults)->get();
            if (count($models) > 0) {
                $results[$resource] = $models;
            }
        }

        return collect($results)->sortKeys()->all();
    }
}
