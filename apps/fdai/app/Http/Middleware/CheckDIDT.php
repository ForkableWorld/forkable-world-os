<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

// App/Http/Middleware/CheckDIDT.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Magic;
use MagicAdmin\Exception\DIDTokenException;
use MagicAdmin\Exception\RequestException;

class CheckDIDT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $all = $request->all();
        $did_token = $request->bearerToken();

        if($did_token){

            try {
                //verifying the DID Token
                Magic::token()->validate($did_token);
                $user = Magic::user()->get_metadata_by_token($did_token);
                if (!$user) {
                    return response()->json(["message" => "Unauthorized user"], 401);
                }
            } catch (DIDTokenException $e) {
                return response()->json(["message" => $e->getMessage()], 401);
            } catch (RequestException $e) {
                return response()->json(["message" => "Request Exception"], 401);
            }

        } else {
            return response()->json(["message" => "Bearer token missing"], 401);
        }

        return $next($request);
    }
}
