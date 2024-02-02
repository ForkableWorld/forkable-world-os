<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\View\View;
use function view;
class ExampleDashboardController extends Controller {
	/**
	 * Create a new controller instance.
	 * @return void
	 */
	public function __construct(){
		$this->middleware('auth');
	}
	/**
	 * Show the application dashboard.
	 * @return View
	 */
	public function index(){
		$user = Auth::user();
		$cards = $user->getCards();
		return view('example-dashboard', ['cards' => $cards]);
	}
}
