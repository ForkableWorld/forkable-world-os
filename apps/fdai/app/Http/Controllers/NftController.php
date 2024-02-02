<?php

namespace App\Http\Controllers;

use App\Exceptions\AccessTokenExpiredException;
use App\Nfts\Traits\Tokenizable;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use Illuminate\Http\Request;
class NftController extends BaseAPIController
{
	public function index(Request $request){
		$user = QMAuth::getQMUser();
		$input = QMRequest::getInput();
		$type = $this->getTokenizableClass();
		$hasMany = $user->nfts();
		if($type){
			$hasMany->where('tokenizable_type', $type);
		}
		$nfts = $hasMany->get();
		if(!$nfts->count()){
			$nft = $this->mint();
			
		}
		return response()->json($nfts);
	}
	protected function getTokenizableClass(): string|null{
		return null;
	}
	protected function mint(){
		$user = QMAuth::getQMUser();
		$input = QMRequest::getInput();
		$type = $this->getTokenizableClass();
		$hasMany = $user->nfts();
		if($type){
			$hasMany->where('tokenizable_type', $type);
		}
		$nfts = $hasMany->get();
		return response()->json($nfts);
	}
	/**
	 * @throws AccessTokenExpiredException
	 */
	public function metadata($id){
		/** @var Tokenizable $model */
		$model = $this->findModel($id);
		$metadata = $model->generateNftMetadata();
		return response()->json($metadata);
	}
}
