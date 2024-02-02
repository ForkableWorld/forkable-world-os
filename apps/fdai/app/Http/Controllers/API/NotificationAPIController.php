<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\Request;
/** Class NotificationController
 * @package App\Http\Controllers\API
 */
class NotificationAPIController extends BaseAPIController {

	public function index(Request $request){
		return parent::index($request);
	}
}
