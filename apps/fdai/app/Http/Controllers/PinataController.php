<?php
namespace App\Http\Controllers;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Web3\PinataClient;
use Request;
class PinataController extends Controller
{
	public function __construct(\Illuminate\Http\Request $request){
		//$this->middleware('auth');
		parent::__construct($request);
	}
	public function post(Request $request)
	{
		$user = QMAuth::getUser();
		$htmlString = QMRequest::getInput('htmlString');
		$name = QMRequest::getInput('name');
		$pinata = new PinataClient();
		$response = $pinata->pinFileToIPFS($htmlString, [
			'name' => $name,
			'life_force' => $user->getLifeForce(),
		]);
		$ipfsCid = $response['IpfsHash'];
		$fileUrl = 'https://ipfs.io/ipfs/'.$ipfsCid;
		// Return the file URL or handle the response as needed
		return $fileUrl;
	
	}
}
