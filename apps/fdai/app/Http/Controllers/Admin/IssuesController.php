<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use function view;
class IssuesController extends Controller {
	public function index(){
		return view('github-issues');
	}
}
