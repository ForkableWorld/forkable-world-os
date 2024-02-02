<?php

namespace App\Http\Controllers;

use App\Slim\Middleware\QMAuth;
class LifeForceController extends Controller
{
    public function index()
    {
        $u = QMAuth::getUser();
		$lifeForce = $u->getLifeForce();
		
    }
	public function metadata(){
		$u = QMAuth::getUser();
		$lifeForce = $u->generateNftMetadataForScoreVariables();
		return $lifeForce;
	}
}
