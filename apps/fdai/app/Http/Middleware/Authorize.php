<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpVoidFunctionResultUsedInspection */
/** @noinspection PhpIncompatibleReturnTypeInspection */
/** @noinspection PhpMissingReturnTypeInspection */
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Astral;
class Authorize
{
    /**
     * Handle the incoming request.
     * @param  Request  $request
     * @param \Closure $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next){
        $authorized = Astral::check($request);
        return $authorized ? $next($request) : abort(403);
    }
}
