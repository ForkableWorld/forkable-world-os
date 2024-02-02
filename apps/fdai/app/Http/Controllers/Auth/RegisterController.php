<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\Auth;
use App\Buttons\Auth\LoginButton;
use App\Exceptions\InvalidUsernameException;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserEmailProperty;
use App\Properties\User\UserPasswordProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Middleware\QMAuth;
use App\Types\QMStr;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;
class RegisterController extends AuthController {
	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	| This controller handles the registration of new users as well as their
	| validation and creation. By default this controller uses a trait to
	| provide this functionality without requiring any additional code.
	*/
	use RegistersUsers;
    public $loginAfterSignUp = true;
	/**
	 * Create a new controller instance.
	 * @param Request $request
	 */
	public function __construct(Request $request){
		$this->middleware('guest');
		parent::__construct($request);
	}
	/**
	 * Get a validator for an incoming registration request.
	 * @param array $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function validator(array $data){
		return Validator::make($data, [
			'name' => [
				'required',
				'string',
				'max:255',
			],
			'email' => [
				'required',
				'string',
				'email',
				'max:255',
				'unique:users',
			],
			'password' => [
				'required',
				'string',
				'min:6',
				'confirmed',
			],
		]);
	}
	/**
	 * Create a new user instance after a valid registration.
	 * @param array $data
	 * @return User
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function create(array $data){
		return User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => UserPasswordProperty::hashPassword($data['password']),
		]);
	}
	/**
	 * Handle a registration request for the application.
	 * @param Request $request
	 * @return RedirectResponse|JsonResponse
     * @noinspection PhpUnused
	 */
	public function postRegister(Request $request){
		$data = $request->all();
        $data = RegisterController::formatRegisterData($data);
        $validator = Validator::make($data, [
			User::FIELD_USER_LOGIN => 'required|min:3|unique:wp_users,' . User::FIELD_USER_LOGIN,
			// TODO: Why do we need this?
			//'display_name' => 'required|min:3',
			User::FIELD_EMAIL => 'required|email|unique:wp_users,' . User::FIELD_EMAIL,
			User::FIELD_PASSWORD => 'required|between:3,32',
			'password_confirmation' => 'required|same:password',
		]);
		if($validator->fails()){
			$message = QMStr::validatorToString($validator);
			if(stripos($message, 'login has already been taken') !== false){
				unset($data[User::FIELD_USER_LOGIN]);
			}
			if(stripos($message, 'email has already been taken') !== false){
				unset($data[User::FIELD_EMAIL]);
				unset($data[User::FIELD_USER_EMAIL]);
			}
			if($request->expectsJson()){
				return Response::json(ResponseUtil::makeError($message), 400);
			}
			return redirect()->back()->withInput()->withErrors($validator);
			//return $this->goBackWithErrorMessageQueryParam($message, $userInfo);
		}
		try {
			$user = User::createNewUserAndLogin($data);
			if($request->expectsJson()){
				$user = User::find($user->ID);
				$qmUser = $user->getQMUser();
				$qmUser->getOrSetAccessTokenString(BaseClientIdProperty::fromRequest() ?? 
				                                   BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
				return Response::json(ResponseUtil::makeResponse('User registered successfully.', 
				                                                 $qmUser), 200);
			}
			return IntendedUrl::getRedirectResponse();
		} catch (InvalidUsernameException $e) {
			if($request->expectsJson()){
				return Response::json(ResponseUtil::makeError($e->getMessage()), 400);
			}
			return redirect()->back()->withInput()->withErrors($e->getMessage());
			//return $this->goBackWithErrorMessageQueryParam($e->getMessage());
		} catch (Exception $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			$m = $e->getMessage();
			if(stripos($m, 'duplicate') !== false){
				$m = "User already exists.  Click the sign in link above or the chat button for help.";
			}
			if($request->expectsJson()){
				return Response::json(ResponseUtil::makeError($m), 400);
			}
			return redirect()->back()->withInput()->withErrors($m);
			//return $this->goBackWithErrorMessageQueryParam($m);
		}
	}

    /**
     * Show the application registration form.
     * @param Request $request
     * @return Factory|View
     * @internal param Request $request
     */
	public function getRegister(Request $request){
		QMAuth::saveLoginRequestParams();
		return $this->getViewWithRequestParams('auth.register', $request);
	}
	/**
	 * @return string
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function loginUsername(){
		return User::FIELD_USER_LOGIN;
	}
	/**
	 * @param Request $request
	 * @return RedirectResponse|\Response
	 */
	public function register(Request $request){
		return $this->postRegister($request);
	}

    /**
     * @param array $data
     * @return array
     */
    public static function formatRegisterData(array $data): array
    {
        $data[User::FIELD_USER_LOGIN] = UserUserLoginProperty::pluckOrDefault($data);  // Handle submission of a synonym
        $email = $data[User::FIELD_EMAIL] = UserEmailProperty::pluckOrDefault($data);
        if (!isset($data[User::FIELD_USER_LOGIN])) {
            $data[User::FIELD_USER_LOGIN] = QMStr::before("@", $email);
        }
        $data[User::FIELD_PASSWORD] = UserPasswordProperty::pluckOrDefault($data);
        if (!isset($data['password_confirmation'])) {
            $data['password_confirmation'] = $data["user_pass_confirmation"] ?? $data["passwordConfirm"] ?? null;
        }
        $data[User::FIELD_DISPLAY_NAME] = $data[User::FIELD_USER_LOGIN];
        return $data;
    }
    public static function apiRegister(){
        $data = RegisterController::formatRegisterData(request()->all());
        try {
            $u = User::createNewUserAndLogin($data);
        } catch (\Illuminate\Database\QueryException $e) {
            return LoginController::apiLogin();
        }
        $qmUser = $u->getQMUser();
        $qmUser->getOrCreateAccessTokenString(BaseClientIdProperty::fromRequest(true));
        return \Response::json(ResponseUtil::makeResponse("New user created", $qmUser), 201);
    }
}
