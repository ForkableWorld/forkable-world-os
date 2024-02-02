<?php

namespace App;

use Illuminate\Http\Request;
trait AuthorizesRequests
{
    /**
     * The callback that should be used to authenticate Astral users.
     *
     * @var \Closure
     */
    public static $authUsing;
	/**
	 * Register the Astral authentication callback.
	 * @param \Closure $callback
	 * @return Astral
	 */
    public static function auth($callback): Astral{
        static::$authUsing = $callback;

        return new static;
    }

    /**
     * Determine if the given request can access the Astral dashboard.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function check(Request $request): bool{
        return (static::$authUsing ?: function () {
            return app()->environment('local');
        })($request);
    }
}
