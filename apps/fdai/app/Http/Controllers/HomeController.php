<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Illuminate\Contracts\Support\Renderable;
class HomeController extends Controller {
	/**
	 * Create a new controller instance.
	 * @return void
	 */
	public function __construct(){
		$this->middleware('auth');
	}
	/**
	 * Show the application dashboard.
	 * @return Renderable
	 */
	public function index(){
		return view('home');
	}
}
