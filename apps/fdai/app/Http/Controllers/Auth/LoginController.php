<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Auth;
use App\Buttons\Auth\LoginButton;
use App\Buttons\Auth\LogoutButton;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Urls\AfterLogoutUrl;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\Alerter;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use InfyOm\Generator\Utils\ResponseUtil;

class LoginController extends AuthController {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
	protected $loginPath = "/".LoginButton::PATH;
	protected $redirectAfterLogout = "/".LoginButton::PATH;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
	/**
	 * Show the application login form.
	 * @param Request $request
	 * @return View
	 * @noinspection PhpUnused
	 */
	public function getLogin(Request $request){
		QMAuth::saveLoginRequestParams();
		if(view()->exists('auth.authenticate')){
			return $this->getViewWithRequestParams('auth.authenticate', $request);
		}
		return $this->getViewWithRequestParams('auth.login', $request);
	}
	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function login(Request $request): RedirectResponse{
		return $this->postLogin($request);
	}
	/**
	 * Handle a login request to the application.
	 * @param LoginRequest|Request $request
	 * @return RedirectResponse
	 * @noinspection PhpUnused
	 */
	public function postLogin(Request $request): RedirectResponse{
		return $this->authenticate($request);
	}
	/**
	 * Handle an authentication attempt.
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function authenticate(Request $request): RedirectResponse{
		if($user = QMAuth::getUserByUserNameAndPassword()){
			return $this->authenticated($request, $user);
		}
		return $this->badCredentialsRedirect();
	}
    public static function apiLogin(){
        $user = QMAuth::getUserByUserNameAndPassword();
        if(!$user){
            return ResponseUtil::makeError('login.error.bad_credentials');
        }
        $user->getOrCreateAccessTokenString(BaseClientIdProperty::fromRequest(true));
        $user->login();
        return \Response::json(ResponseUtil::makeResponse("User logged in", $user->getQMUser()), 201);
    }
	/**
	 * The user has been authenticated.
	 * @param Request|\Request $request
	 * @param mixed $user
	 * @return RedirectResponse
	 */
	protected function authenticated(Request $request, $user): RedirectResponse{
		/** @var User $user */
		$user->login(QMRequest::getBool('remember'));
		$this->clearLoginAttempts($request);
		return IntendedUrl::getRedirectResponse();
	}
	/**
	 * @return RedirectResponse
	 */
	protected function badCredentialsRedirect(): RedirectResponse{
		$message = QMAuth::LOGIN_FAILURE_MESSAGE;
		QMLog::error("Login failed!", [], true, $message);
		Alerter::errorWithHelpButtonToast($message);
		$back = redirect()->back();
		$location = $back->headers->get('Location') ?? null;
		if(empty($location) || !QMStr::contains($location, "login"))    {
			$back->header('Location', LoginButton::url(qm_request()->query()));
		}
		return $back->withInput()
		            ->withErrors(['error' => $message]);
	}
	/**
	 * Log the user out of the application.
	 * @return Redirector|RedirectResponse
	 */
	public function getLogout(){
		/** @var User $user */
		if($user = Auth::user()){
			$user->unsetCustomProperties();
		}
		$this->logout();
		if($this->getRequest()->get('close', false)){
			echo "<script>window.close();</script>";
		}
		// Doesn't work - currently, user won't reach close = true window.close() only works if the script opened the window
		if(AuthController::onClientAuthorizationPage()){
			return LogoutButton::getRedirect();
		}
		return redirect(AfterLogoutUrl::url());
	}
	public function logout(){
		QMAuth::logout(__METHOD__);
	}
}
