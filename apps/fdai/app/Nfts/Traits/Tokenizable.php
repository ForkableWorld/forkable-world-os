<?php

namespace App\Nfts\Traits;

use Illuminate\Database\Eloquent\Model;

trait Tokenizable
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isTokenizedBy(Model $user): bool
    {
        if (\is_a($user, config('auth.providers.users.model'))) {
            if ($this->relationLoaded('tokenizers')) {
                return $this->tokenizers->contains($user);
            }

            return $this->tokenizers()->where('user_id', $user->getKey())->exists();
        }

        return false;
    }

    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tokenizers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            config('tokenize.nfts_table'),
            'tokenizable_id',
            'user_id'
        )
            ->where('tokenizable_type', $this->getMorphClass());
    }
	abstract public function generateNftMetadata(): array;
}
