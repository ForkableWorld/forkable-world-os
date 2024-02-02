<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingReturnTypeInspection */
namespace App\Http\Controllers;
use App\Models\BaseModel;
use App\Storage\QueryBuilderHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceDetailRequest;
class ResourceShowController extends Controller
{
    /**
     * Display the resource for administration.
     * @param ResourceDetailRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function handle(ResourceDetailRequest $request){
        AstralRequest::setInMemory($request);
		try {
			$resource = $request->newResourceWith(tap($request->findModelQuery(),
				function ($query) use ($request) {
					$request->newResource()->detailQuery($request, $query);
				})->firstOrFail());
		} catch (\Throwable $e){
			$mq = $request->findModelQuery();
			$qb = $mq->getQuery();
			$sql = QueryBuilderHelper::toPreparedSQL($qb);
			/** @var BaseModel $model */
			$model = $mq->getModel();
			$wheres = $qb->wheres;
			$where = QueryBuilderHelper::getHumanizedWhereClause($wheres);
			$model->popupLinkToIndex($e->getMessage()." where ".$sql);
			le($e);
		}
        $resource->authorizeToView($request);
        return response()->json([
            'panels' => $resource->availablePanelsForDetail($request),
            'resource' => $resource->serializeForDetail($request),
        ]);
    }
}
