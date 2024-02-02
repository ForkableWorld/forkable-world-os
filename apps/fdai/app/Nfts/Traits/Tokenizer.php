<?php

namespace App\Nfts\Traits;

use App\Models\Nft;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
trait Tokenizer
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     *
     * @return Nft
     */
    public function tokenize(Model $object): Nft
    {
        $attributes = [
            'tokenizable_type' => $object->getMorphClass(),
            'tokenizable_id' => $object->getKey(),
            'user_id' => $this->getKey(),
        ];

        /* @var \Illuminate\Database\Eloquent\Model $tokenize */
        $tokenize = \app(\App\Models\Nft::class);

        /* @var \App\Nfts\Traits\Tokenizable|\Illuminate\Database\Eloquent\Model $object */
        return $tokenize->where($attributes)->firstOr(
            function () use ($tokenize, $attributes) {
                return $tokenize->unguarded(function () use ($tokenize, $attributes) {
                    if ($this->relationLoaded('nfts')) {
                        $this->unsetRelation('nfts');
                    }

                    return $tokenize->create($attributes);
                });
            }
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     *
     * @return bool
     * @throws \Exception
     */
    public function untokenize(Model $object): bool
    {
        /* @var \App\Models\Nft $relation */
        $relation = \app(\App\Models\Nft::class)
            ->where('tokenizable_id', $object->getKey())
            ->where('tokenizable_type', $object->getMorphClass())
            ->where('user_id', $this->getKey())
            ->first();

        if ($relation) {
            if ($this->relationLoaded('nfts')) {
                $this->unsetRelation('nfts');
            }

            return $relation->delete();
        }

        return true;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @return Nft|bool|null
     * @throws \Exception
     */
    public function toggleTokenize(Model $object)
    {
        return $this->hasTokenized($object) ? $this->untokenize($object) : $this->tokenize($object);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     *
     * @return bool
     */
    public function hasTokenized(Model $object): bool
    {
        return ($this->relationLoaded('nfts') ? $this->nfts : $this->nfts())
                ->where('tokenizable_id', $object->getKey())
                ->where('tokenizable_type', $object->getMorphClass())
                ->count() > 0;
    }

    public function nfts(): HasMany
    {
        return $this->hasMany(\App\Models\Nft::class, 'user_id', $this->getKeyName());
    }

    /**
     * Get Query Builder for nfts
     *
     */
    public function getTokenizedItems(string $model)
    {
        return app($model)->whereHas(
            'tokenizers',
            function ($q) {
                return $q->where('user_id', $this->getKey());
            }
        );
    }

    public function attachTokenizeStatus($tokenizables, callable $resolver = null)
    {
        $returnFirst = false;
        $toArray = false;

        switch (true) {
            case $tokenizables instanceof Model:
                $returnFirst = true;
                $tokenizables = \collect([$tokenizables]);
                break;
            case $tokenizables instanceof LengthAwarePaginator:
                $tokenizables = $tokenizables->getCollection();
                break;
            case $tokenizables instanceof Paginator:
                $tokenizables = \collect($tokenizables->items());
                break;
            case \is_array($tokenizables):
                $tokenizables = \collect($tokenizables);
                $toArray = true;
                break;
        }

        \abort_if(!($tokenizables instanceof Collection), 422, 'Invalid $tokenizables type.');

        $tokenized = $this->nfts()->get()->keyBy(function ($item) {
            return \sprintf('%s-%s', $item->tokenizable_type, $item->tokenizable_id);
        });

        $tokenizables->map(function ($tokenizable) use ($tokenized, $resolver) {
            $resolver = $resolver ?? fn ($m) => $m;
            $tokenizable = $resolver($tokenizable);

            if ($tokenizable && \in_array(Tokenizable::class, \class_uses_recursive($tokenizable))) {
                $key = \sprintf('%s-%s', $tokenizable->getMorphClass(), $tokenizable->getKey());
                $tokenizable->setAttribute('has_tokenized', $tokenized->has($key));
            }
        });

        return $returnFirst ? $tokenizables->first() : ($toArray ? $tokenizables->all() : $tokenizables);
    }
}
