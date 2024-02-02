<?php

namespace App\Casts;
use App\Types\QMStr;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class OAuthAuthenticatorCast implements \Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes,
                                        CastsAttributes
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
	    return OAuthAuthenticatorCast::decode($value);
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
		if(is_string($value)){
			return $value;
		}

        return serialize($value);
    }

	public function serialize($model, string $key, $value, array $attributes): ?string{
		if (is_null($value)) {
			return null;
		}

		return serialize($value);
	}
	/**
	 * @param mixed $value
	 * @return mixed|string|null
	 */
	public static function decode(mixed $value): mixed{
		if(is_null($value)){
			return null;
		}
		if(!is_string($value)){
			return $value;
		}
		if($decoded = QMStr::isJson($value)){
			return $decoded;
		}
		$unserialize = unserialize($value, ['allowed_classes' => true]);
		return $unserialize;
	}
}
