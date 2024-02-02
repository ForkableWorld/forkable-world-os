<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use App\Http\Requests\ActionRequest;
use App\Http\Requests\AstralRequest;
class ActionController extends Controller
{
    /**
     * List the actions for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        AstralRequest::setInMemory($request);
        return response()->json([
            'actions' => $request->newResource()->availableActions($request),
            'pivotActions' => [
                'name' => $request->pivotName(),
                'actions' => $request->newResource()->availablePivotActions($request),
            ],
        ]);
    }
    /**
     * Perform an action on the specified resources.
     *
     * @param \App\Http\Requests\ActionRequest $request
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\MissingActionHandlerException
     */
    public function store(ActionRequest $request)
    {
        AstralRequest::setInMemory($request);
        $request->validateFields();
        return $request->action()->handleRequest($request);
    }
}
