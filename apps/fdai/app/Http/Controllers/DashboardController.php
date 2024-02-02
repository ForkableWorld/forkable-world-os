<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Auth;
class DashboardController extends Controller {
	/**
	 * Create a new controller instance.
	 * @return void
	 */
	public function __construct(){
		$this->middleware('auth');
	}
	/**
	 * Show the application dashboard.
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function index(\Illuminate\Http\Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View{
		$user = Auth::user();
		$cards = $user->getCards($request);
		return view('user-dashboard', ['cards' => $cards]);
	}
}
