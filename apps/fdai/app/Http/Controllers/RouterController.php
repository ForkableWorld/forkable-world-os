<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use App\Http\Requests\AstralRequest;
class RouterController extends Controller
{
	/**
	 * Display the Astral Vue router.
	 * @param Request $request
	 * @return View
	 */
    public function show(Request $request): View{
        AstralRequest::setInMemory($request);
        return view('astral::router', [
            'user' => $request->user(),
        ]);
    }
}
