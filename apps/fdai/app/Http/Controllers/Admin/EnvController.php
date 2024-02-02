<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Utils\Env;
use Illuminate\Http\JsonResponse;
class EnvController extends Controller {
	public function get(){
		return new JsonResponse([
            'env' => config('app.env'),
            'APP_URL' => Env::getAppUrl(),
            'DB_DATABASE' => Env::get(Env::DB_DATABASE),
            'php' => PHP_VERSION,
        ]);
	}
}
