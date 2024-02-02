<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EncryptedOAuthAuthenticatorCast implements CastsAttributes, 
                                                 \Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes
{
    /**
     * Cast the given value.
     *
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        return unserialize(decrypt($value), ['allowed_classes' => true]);
    }

    /**
     * Prepare the given value for storage.
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return string|null
     */
	public function set($model, string $key, $value, array $attributes): ?string{
        if (is_null($value)) {
            return null;
        }

        return encrypt(serialize($value));
    }
	public function serialize($model, string $key, $value, array $attributes): ?string{
		if (is_null($value)) {
			return null;
		}

		return encrypt(serialize($value));
	}
}
