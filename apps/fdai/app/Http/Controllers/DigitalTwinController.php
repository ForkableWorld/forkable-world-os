<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class DigitalTwinController extends NftController
{
	protected function getTokenizableClass(): string|null{
		return User::class;
	}
	public function index(Request $request){
		$user = QMAuth::getUser();
		$meta = $user->generateNftMetadata();
		return response()->json($meta);
	}
	/**
	 * @param $id
	 * @return JsonResponse|object
	 * @throws \Exception
	 */
	public function show($id = null){
		if(is_string($id) && str_contains('x', $id)){
			$user = User::findByEthAddress($id);
		} else {
			if(!$id){
				$user = QMAuth::getUser();
			} else {
				$user = User::findInMemoryOrDB($id);
			}
		}
		$metadata = $user->generateNftMetadata();
		return response()->json($metadata);
	}
}
