<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\CardRequest;

class CardController extends Controller
{
    /**
     * List the cards for the given resource.
     *
     * @param  \App\Http\Requests\CardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(CardRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return $request->availableCards();
    }
}
