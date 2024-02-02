<?php
namespace App\Http\Controllers\Auth;
use App\Buttons\Auth\AuthButton;
use App\Buttons\States\OnboardingStateButton;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Urls\FinalCallbackUrl;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\Nonce;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Utils\Env;
use Elliptic\EC;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use kornrunner\Keccak;
class AuthController extends Controller {
	/**
	 * @return string
	 */
	public static function getParentHostAuthUrlPrefixWithoutProtocol(): string{
		$currentUrl = QMRequest::current();
		$appHostName = self::getParentAppHostNameEnvWithoutHttpsProtocol();
		if(str_starts_with($currentUrl, "http://localhost")){
			$appHostName = "localhost";
		} // Needed for testing
		$parentAuthUrl = $appHostName . AuthButton::PATH;
		return $parentAuthUrl;
	}
	/**
	 * @return string
	 */
	protected function redirectTo(): string{
		return IntendedUrl::get() ?? OnboardingStateButton::url();
	}
	/**
	 * @return bool
	 * @internal param array $PAGINATION_PARAMS
	 */
	public static function onParentAuthorizationPage(): bool{
		$currentUrlWithoutProtocol = QMRequest::urlWithoutProtocol();
		$parentAuthUrlWithoutProtocol = self::getParentHostAuthUrlPrefixWithoutProtocol();
		$onHostAuthPage = stripos($currentUrlWithoutProtocol, $parentAuthUrlWithoutProtocol) === 0;
		$message =
			"Current url is $currentUrlWithoutProtocol and parent app host auth url is $parentAuthUrlWithoutProtocol";
		if(!$onHostAuthPage){
			QMLog::debug("$message so going to redirect");
		} else{
			QMLog::debug("$message so NOT going to redirect");
		}
		return $onHostAuthPage;
	}
	/**
	 * Needs to exclude protocol checking because load balancer won't pass https through
	 * @return bool
	 */
	protected static function onClientAuthorizationPage(): bool{
		$onAuthPage = str_starts_with(QMRequest::urlWithoutProtocol(), Request::getHost().AuthButton::PATH);
		return !self::onParentAuthorizationPage() && $onAuthPage;
	}
	/**
	 * @return string
	 */
	private static function getParentAppHostNameEnvWithoutHttpsProtocol(): string{
		$appHostName = QMRequest::origin();
		$appHostName = str_replace("https://", "", $appHostName);
		/** @noinspection HttpUrlsUsage */
		$appHostName = str_replace("http://", "", $appHostName);
		return $appHostName;
	}
	public function final_callback_url(){
		return FinalCallbackUrl::getIfSet();
	}
	public function session(){
		return FinalCallbackUrl::getIfSet();
	}
	public static function nonce(string $type){
		$address = AuthController::getEthAddress();
		$content = AuthController::getNonceContent($address, $type);
		$nonceValue = Hash::make($content);
		$nonceModel = Nonce::create([
		   'nonce' => $nonceValue,
		   'content' => $content,
		   'type' => $type
		]);
		return AuthController::json_response(['nonce' => $nonceModel->nonce,], 200);
	}
	public static function verifySignature(string $signature, string $address, string $type){
		$content = AuthController::getNonceContent($address, $type);
		$nonce = Nonce::where('content', $content)
		              ->where('type', $type)
		              ->latest()
		              ->first();
		if (!$nonce) {le("Nonce not found for address $address and type $type");}
		return $nonce->verifyAndDelete($signature, $address);
	}
	/**
	 * @return JsonResponse
	 * @throws UnauthorizedException
	 */
	public static function web3Connect(){
		$address = AuthController::getEthAddress();
		$type = 'connect';
		$verify = AuthController::verifySignature(AuthController::getSignature(), $address, $type);
		if (!$verify) {
			return AuthController::deleteNonceAndRespondWithError($address, 'Invalid address or signature', $type);
		}
		$user = QMAuth::getUser();
		if(!$user){throw new UnauthorizedException("No user found to connect wallet to");}
		if($user->eth_address === $address){
			return AuthController::json_response([
				                                     'message' => "User address already set to $address",
				                                     'user' => $user->getQMUser(),
			                                     ], 400);
		}
		$user->eth_address = $address;
		try {$user->save();} catch (ModelValidationException $e) {le($e);}
		return AuthController::json_response([
			                                     'message' => "Set address $address for user with id $user->id",
			                                     "user" => $user->getQMUser(),
		                                     ], 201);
	}
	public function web3Register(){
		$address = AuthController::getEthAddress();
		$verify = AuthController::verifySignature(AuthController::getSignature(), $address, 'register');
		if(!$verify){
			return AuthController::deleteNonceAndRespondWithError($address, 'Invalid address or signature', $type);
		}
		if($user = QMAuth::getUser()){
			try {return AuthController::web3Connect();} catch (UnauthorizedException $e) {le($e);}
		}
		if($user = AuthController::userByEthAddress($address)){
			return AuthController::json_response(['message' => "User with address $address already exists",], 400);
		}
		$user = User::createByEthAddress($address);
		return AuthController::json_response([
			                                     'message' => 'Registration successful',
			                                     'token' => $user->createApiToken(),
			                                     'address' => $address,
			                                     'user' => $user->getQMUser(),
		                                     ], 201);
	}
	public function web3Login(){
		$address = AuthController::getEthAddress();
		$verify = AuthController::verifySignature(AuthController::getSignature(), $address, 'login');
		if ($verify) {
			$user = AuthController::userByEthAddress($address);
			if ($user) {
				$user->login(true);
				return AuthController::json_response([
					                                     'message' => 'Login successful',
					                                     'token' => $user->createApiToken(),
					                                     'address' => $address,
					                                     'user' => $user->getQMUser(),
				                                     ], 201);
			}
		}
		return AuthController::deleteNonceAndRespondWithError($address, 'There is no user with that wallet address',
		                                                      $type);
	}
	protected static function json_response(array $array, int $status){
		return response()->json($array, $status);
	}
	/**
	 * @param mixed $address
	 * @param string $msg
	 * @param string $type
	 * @return \Illuminate\Http\JsonResponse
	 */
	private static function deleteNonceAndRespondWithError(string $address, string $msg, string $type): 
	\Illuminate\Http\JsonResponse{
		Nonce::where('content', $address.'-'.$type)
		     ->where('type', $type)
		     ->delete();
		return response()->json([
            'message' => $msg
        ], 400);
	}
	/**
	 * @param $address
	 * @param mixed $type
	 * @return string
	 */
	private static function getNonceContent($address, mixed $type): string{
		return $address.'-'.$type.'-'.Str::slug(Env::getRequired('APP_KEY'));
	}
	/**
	 * @return string|null
	 */
	private static function getEthAddress(): ?string {
		$address = QMRequest::getInput('eth_address');
		return $address;
	}
	/**
	 * @return string|null
	 */
	private static function getSignature(): ? string {
		$address = QMRequest::getInput('signature');
		return $address;
	}
	/**
	 * @param mixed $address
	 * @return User|null
	 */
	private static function userByEthAddress(mixed $address): null|User{
		return User::where('eth_address', $address)->first();
	}
}
