<?php /** @noinspection RegExpRedundantEscape */

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class EloquentWherelikeServiceProvider extends ServiceProvider {

    /**
     * Recursively resolve the chained relational dependency
     *
     * @param Builder $builder
     * @param mixed $searchTerm
     * @param array $relations
     * @param bool $withTrash
     * @param string $relationalCondition
     *
     * @return Builder
     */
    public function resolveRelationDependency(   Builder $builder,
                                                            $searchTerm,
                                                    array   $relations           = [],
                                                    bool    $withTrash           = false,
                                                    string  $relationalCondition = 'orWhereHas'): Builder
    {

        $self = $this;

        if ( empty($relations) ) {

			return $builder;
		}

        $relation = array_shift($relations);

        $relationParams  = Str::contains($relation, '[') && Str::contains($relation, ']')
                               ? explode(',' ,preg_replace('/(.*)\[(.*)\](.*)/sm', '\2', $relation))
                               : [];
        $relationName    = preg_replace('/\[[\s\S]+?\]/', '', $relation);

        return
            $builder->{$relationalCondition}($relationName, function ($query) use ($builder, $relationParams, $searchTerm, $relations, $withTrash, $self) {

                $query = $self->resolveRelationDependency($query, $searchTerm, $relations, $withTrash, 'whereHas');

                $method = 'where';

                foreach ($relationParams as $relParam) {

                    if ( $withTrash ) {

                        $query = $query->withTrashed($builder);
                    }

                    $like = $this->getLikeOperator($query);
                    $query->{$method}(
                        trim($relParam),
                        $like,
                        "%{$searchTerm}%"
                    );

                    $method = 'orWhere';
                }
            });
    }


    /**
     * Register services.
     *
     * @return void
     */
    public function register() {

    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {

        $self = $this;

        Builder::macro('whereLike', function ($attributes, $searchTerm, bool $withTrash = false) use ($self) {

            $this->where(function (Builder $query) use ($attributes, $searchTerm, $withTrash, $self) {

                $searchTerms = array_map('trim', explode(' ', $searchTerm));

                foreach ($searchTerms as $searchTerm) {

                    foreach (Arr::wrap($attributes) as $attribute) {

                        $query->when(
                            Str::contains($attribute, '.'),

                            function (Builder $query) use ($attribute, $searchTerm, $withTrash, $self) {

                                $relations = explode('.', $attribute);

                                array_shift($relations);

                                $self->resolveRelationDependency($query, $searchTerm, $relations, $withTrash);
                            },

                            function (Builder $query) use ($attribute, $searchTerm) {

                                $query->orWhere(
                                    $attribute,
                                    $this->getLikeOperator($query),
                                    "%{$searchTerm}%"
                                );
                            }
                        );
                    }
                }
            });

            return $this;
        });
    }

    /**
     * @param Builder $query
     * @return string
     */
    function getLikeOperator(Builder $query): string
    {
        $like = 'LIKE';
        if ($query->getConnection()->getDriverName() == 'pgsql') {
            $like = 'ILIKE';
        }
        return $like;
    }

}
