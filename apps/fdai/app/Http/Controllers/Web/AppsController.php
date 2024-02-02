<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Web;
use App\AppSettings\AppSettings;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\SecretException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Logging\QMLog;
use App\Mail\CollaboratorInvitationEmail;
use App\Mail\TooManyEmailsException;
use App\Models\Application;
use App\Models\BillingPlan;
use App\Models\Collaborator;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Services\OauthService;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use App\Storage\S3\S3Images;
use App\Types\QMStr;
use Auth;
use DB;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lang;
use Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use View;
class AppsController extends Controller {
	const APPLICATION_TYPE_PHYSICIAN = 'physicians';
	const APPLICATION_TYPE_APPS = 'apps';
	const APPLICATION_TYPE_STUDIES = 'studies';
	public $clientOrAppId;
	public static $applicationType;
	private $application;
	/**
	 * @return string
	 */
	private function getAppTypeFromRoute(): string{
		$user = Auth::user();
		if(empty($user->avatar_image)){
			QMLog::error("No avatar on $user");
		}
		$folder = 'apps';
		if($this->getRequest()->is('*stud*')){
			$folder = 'studies';
		}
		if($this->getRequest()->is('*physician*')){
			$folder = 'physicians';
		}
		self::$applicationType = $folder;
		return $folder;
	}
	/**
	 * @return bool
	 */
	public function routeIsStudy(): bool{
		return $this->getAppTypeFromRoute() === self::APPLICATION_TYPE_STUDIES;
	}
	/**
	 * @return bool
	 */
	public function routeIsPhysician(): bool{
		return $this->getAppTypeFromRoute() === self::APPLICATION_TYPE_PHYSICIAN;
	}
	/**
	 * @return bool
	 */
	public function routeIsApps(): bool{
		return $this->getAppTypeFromRoute() === self::APPLICATION_TYPE_APPS;
	}

    /**
     * Show a list of all the apps.
     * @return \Illuminate\Contracts\View\View|JsonResponse|RedirectResponse
     * @throws UnauthorizedException
     * @throws InvalidEmailException
     * @throws NoEmailAddressException
     */
	public function getIndex(){
		$user = QMAuth::getQMUser();
		if($this->routeIsPhysician()){
			$clientId = $user->getOrCreatePhysicianApp()->getClientId();
			$path = 'update/' . Str::singular($this->getAppTypeFromRoute());
			QMLog::info("Redirecting from index list to existing $clientId physician application at $path");
			return Controller::redirectWithAccessToken($path, ['clientOrAppId' => $clientId]);
		}
		$applications = $this->getAppRowsForCurrentUser();
		if($this->weShouldReturnJsonResponse()){
			return response()->json([
				'message' => "Got applications",
				'success' => true,
				'data' => ['applications' => $applications],
			], 200);
		}
		return View::make('admin/' . $this->getAppTypeFromRoute() . '/index', compact('applications'));
	}
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function getCreate(): \Illuminate\Contracts\View\View{
		return View::make('admin/' . $this->getAppTypeFromRoute() . '/create'); // Show the page
	}
	/**
	 * @return mixed
	 */
	private function getClientOrAppIdFromRequest(): ?string{
		$inputs = $this->getRequest()->all();
		if(isset($inputs['qmClientId'])){
			return $inputs['qmClientId'];
		}
		if(isset($inputs['clientId'])){
			return $inputs['clientId'];
		}
		if(isset($inputs['client_id'])){
			return $inputs['client_id'];
		}
		return null;
	}
	/**
	 * @param \Illuminate\Validation\Validator|Validator|\Illuminate\Contracts\Validation\Validator $validator
	 * @return string
	 */
	private function getErrorMessage($validator): ?string{
		foreach($validator->errors()->getMessages() as $value){
			foreach($value as $subValue){
				return $subValue;
			}
		}
		return null;
	}
	/**
	 * @return array
	 */
	public function getUnderscoreInputs(): array{
		$inputs = $this->getRequest()->all();
		if(isset($inputs['qmClientId'])){
			$inputs['client_id'] = $inputs['qmClientId'];
		}
		$inputs = QMStr::convertKeysToUnderscore($inputs);
		if(isset($inputs['app_display_name']) && !isset($inputs['client_id']) && AppsController::routeIsApps()){
			$inputs['client_id'] = strtolower(str_replace(' ', '-', $inputs['app_display_name']));
		}
		if(isset($inputs['client_id'])){
			$inputs['client_id'] = BaseClientIdProperty::sanitize($inputs['client_id']);
		}
		return $inputs;
	}
	/**
	 * @param User $user
	 * @return array
	 */
	private function getAppsRules(User $user = null): array{
		if(!$user){
			$user = Auth::user();
		}
		if(!$user){
			throw new UnauthorizedException("No user found");
		}
		$rules = [
			'app_display_name' => 'required|max:100',
			'app_description' => 'max:140',
			'client_id' => 'max:80|unique:oa_clients,client_id|unique:applications,client_id',
		];
		if(!$user->isAdmin()){
			$rules['app_display_name'] = 'required|max:100';
		}
		if($this->routeIsStudy()){
			$rules['outcome_variable_id'] = 'required';
			$rules['predictor_variable_id'] = 'required';
		}
		if($this->routeIsApps()){ // Lots of requirements so people don't use up subdomains/client ids willy-nilly
			$rules['homepage_url'] = 'required|max:255';
			$rules['app_description'] = 'required|max:140';
			$rules['client_id'] = 'required|max:80|unique:oa_clients,client_id|unique:applications,client_id';
		}
		return $rules;
	}
	/**
	 * Application create form processing.
	 * @param OauthService $oauthService
	 * @return RedirectResponse|Response|ResponseFactory
	 * @throws ClientNotFoundException
	 * @throws ModelValidationException
	 */
	public function postCreate(OauthService $oauthService){
		/** @var User $user */
		$user = Auth::user();
		$rules = $this->getAppsRules($user);
		$inputs = $this->getUnderscoreInputs();
		if($this->routeIsPhysician()){
			$application = $oauthService->createPhysicianApplication($user);
		} else{
			$validator = Validator::make($inputs, $rules);
			if($validator->fails()){
				if($this->getErrorMessage($validator)){
					$message = $this->getErrorMessage($validator);
				} else{
					$message = Lang::get($this->getAppTypeFromRoute() . '/message.error.create');
				}
				$fails = $validator->failed();
				if(isset($fails['client_id'])){
					$message = "App name already taken.  Please try another. " . QMStr::CONTACT_MIKE_FOR_HELP_STRING;
				}
				return $this->getFailedAppUpdateResponse($message);
				// redirect()->back()->withInput()->withErrors($validator);
			}
			$appData = [
				'app_display_name' => $inputs['app_display_name'],
				'app_description' => $inputs['app_description'],
				'plan_id' => BillingPlan::free()->id,
				'homepage_url' => $inputs['homepage_url'],
			];
			if($this->routeIsStudy()){
				$appData['outcome_variable_id'] = $this->getRequest()->get('outcome_variable_id');
				$appData['predictor_variable_id'] = $this->getRequest()->get('predictor_variable_id');
				$application = $oauthService->createStudyApplication($appData, $user);
			} else{
				$application = $oauthService->createClientApplication($appData, $user, $inputs['client_id']);
			}
		}
		$this->saveImages($application); //uploads logo, ios and android screenshots to cloudinary
		$design = $application->app_design;
		if($design && !is_string($design)){
			$application->app_design = json_encode($design);
		}
		$this->clientOrAppId = $application->client_id;
		//CloudFlareHelper::createDnsRecord($application->client_id);  // Don't need this anymore
		$application->save();
		// Create demo users
		QMAccessToken::getOrCreateToken($application->client_id, 1, RouteConfiguration::SCOPE_READ_MEASUREMENTS,
			365 * 86400);
		QMAccessToken::getOrCreateToken($application->client_id, $user->getId(),
			'readmeasurements writemeasurements', 365 * 86400);
		return $this->getSuccessfulAppUpdateResponse(Lang::get($this->getAppTypeFromRoute() .
			'/message.success.create'), 201);
	}
	/**
	 * App update.
	 * @param int|string|null $clientOrAppId
	 * @return \Illuminate\Contracts\View\View|RedirectResponse|Response|ResponseFactory
	 * @internal param Route $route
	 */
	public function getIntegration($clientOrAppId = null){
		$application = $this->getApp($clientOrAppId);
        if(!$application){
            return $this->getFailedAppUpdateResponse("App $clientOrAppId not found");
        }
		if(!Collaborator::userIsCollaboratorOrAdmin($application->client_id)){
 			return $this->getNotCollaboratorResponse();
		}
		try {
			$client = $application->getClient();
			$collaborators = $client->getCollaborators();
			$uris = $client->getRedirectUrisWithLineBreaks();
			$requestCount = $application->getRequestCountFractionString();
			$currentPlan = $application->getBillingPlan();
			$userCount = $application->countUsers();
			$plans = BillingPlan::all();
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			Log::error('Error on getting app:' . $e->getMessage(), [$e]);
			return $this->getAppNotFoundResponse(__METHOD__.": ".$e->getMessage()); // Redirect to the apps management page
		}
		$loggedInUser = QMAuth::getUser();
		return View::make('docs/integration-guide',
			compact('application', 'client', 'collaborators', 'uris', 'requestCount', 'plans', 'currentPlan',
				'userCount', 'loggedInUser'));
	}
	/**
	 * App update.
	 * @param int|string|null $clientOrAppId
	 * @return \Illuminate\Contracts\View\View|RedirectResponse|Response|ResponseFactory
	 */
	public function getEdit($clientOrAppId = null){
		$type = $this->getAppTypeFromRoute();
		$application = $this->getApp($clientOrAppId);
		if(!$application){
			return $this->getAppNotFoundResponse();
		}
		if(!Collaborator::userIsCollaboratorOrAdmin($application->client_id)){
			return $this->getNotCollaboratorResponse();
		}
		try {
			if($application->study == 1 && $type == 'apps'){
				return $this->getAppNotFoundResponse();
			}
			$redirect = $application->getOrCreateClient()->getRedirectUri();
			$uris = str_replace(' ', "\r\n", $redirect);
			if($this->routeIsApps()){
				$requestCount = $application->getRequestCountFractionString();
			} else{
				$requestCount = null;
			}
			$plans = BillingPlan::get();
			$currentPlan = $application->plan_id;
			$client = $application->getClient();
			$users = $client->getAbbreviatedUsers();
			$userCount = count($users);
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			Log::error('Error getting app: ' . $e->getMessage(), [$e]);
			if($type == "physicians"){
				return $this->getFailedAppUpdateResponse($e->getMessage(), "account");
			}
			return $this->getFailedAppUpdateResponse($e->getMessage(),
				$this->getAppTypeFromRoute()); // Redirect to the apps management page
		}
		$collaborators = false;
		$loggedInUser = QMAuth::getUser();
		return View::make('admin/' . $type . '/edit',
			compact('application', 'client', 'collaborators', 'uris', 'requestCount', 'plans', 'currentPlan',
				'userCount', 'users', 'loggedInUser'));
	}
	/**
	 * Role update form processing page.
	 * @param int|string|null $clientOrAppId
	 * @return JsonResponse|RedirectResponse|Response|ResponseFactory
	 * @throws ClientNotFoundException
	 * @throws ModelValidationException
	 */
	public function postEdit($clientOrAppId = null){
		$application = $this->getApp($clientOrAppId);
		if(!$application){
			return $this->getAppNotFoundResponse();
		}
		/** @var User $user */
		$user = Auth::user();
		$inputs = QMStr::convertKeysToUnderscore($this->getRequest()->all());
		$validationRules = [  // Declare the rules for the form validation
			'app_description' => 'max:140',
			'app_display_name' => 'max:100',
			'long_description' => 'max:2000',
		];
		if($this->routeIsStudy()){
			$validationRules['outcome_variable_id'] = 'required';
			$validationRules['predictor_variable_id'] = 'required';
			$application->outcome_variable_id = $this->getRequest()->get('outcome_variable_id');
			$application->predictor_variable_id = $this->getRequest()->get('predictor_variable_id');
		}
		$validator = Validator::make($inputs, $validationRules);
		if($validator->fails()){
			if(!$this->weShouldReturnJsonResponse()){
				return redirect()->back()->withInput()->withErrors($validator);
				//return $this->goBackWithErrorMessageQueryParam($validator, $inputs);
			}
			$response = [
				'error' => $validator->messages(),
				'message' => $validator->messages(),
				'success' => false,
			];
			return response()->json($response, 400);
		}
		if(isset($inputs['app_description'])){
			$application->app_description = $inputs['app_description'];
		}
		if(isset($inputs['homepage_url'])){
			$application->homepage_url = $inputs['homepage_url'];
		}
		if(isset($inputs['app_display_name'])){
			$application->app_display_name = $inputs['app_display_name'];
		}
		if(isset($inputs['long_description'])){
			$application->long_description = $inputs['long_description'];
		}
		if($user->isAdmin()){
			$application->enabled = $this->getRequest()->get('enabled');
			$application->billing_enabled = $this->getRequest()->get('billing_enabled');
		}
		if($this->getRequest()->get('publish')){
			if($application->app_status == 'Published'){
				$application->app_status = 'Unpublished';
			} else{
				$application->app_status = 'Published';
			}
		}
		$this->saveImages($application);  //uploads logo, ios and android screenshots to cloudinary
		$client = $application->getClient();
		if(isset($inputs['redirect_uris']) && $inputs['redirect_uris'] !== $client->redirect_uri){
			$client->redirect_uri = $inputs['redirect_uris'];
			$client->save();
		}
		if(is_object($application->app_design)){
			$application->app_design = json_encode($application->app_design);
		}
		$this->application = $application;
		if($application->save()){
			Application::deleteAppSettingsFromMemcached($application->client_id);
			return $this->getSuccessfulAppUpdateResponse(null, 201);
		} else{
			return $this->getFailedAppUpdateResponse("Could not save application");
		}
	}
	/**
	 * @param string|null $message
	 * @param int|null $code
	 * @return ResponseFactory|RedirectResponse|Response
	 * @throws ClientNotFoundException
	 */
	private function getSuccessfulAppUpdateResponse(string $message = null, int $code = 200){
		if(!$message){
			$message = Lang::get($this->getAppTypeFromRoute() . '/message.success.update');
		}
		$clientOrAppId = $this->getClientOrAppId();
		$redirectionRoute = 'update/'.Str::singular($this->getAppTypeFromRoute());
		return $this->getResponse($redirectionRoute,
		                          ['clientOrAppId' => $clientOrAppId],
		                          ['appSettings' => Application::getClientAppSettings($clientOrAppId)], 'success', $message,
		                          $code);
	}
	/**
	 * @param null $message
	 * @return JsonResponse|RedirectResponse
	 */
	private function getAppNotFoundResponse($message = null){
		if(!$message){
			$message = "Application not found!";
		}
		return $this->getFailedAppUpdateResponse($message, $this->getAppTypeFromRoute());
	}
	/**
	 * @return JsonResponse|RedirectResponse
	 */
	private function getNotCollaboratorResponse(){
		$type = $this->getAppTypeFromRoute();
		$typeSingular = Str::singular($type);
		return $this->getFailedAppUpdateResponse("You are not a collaborator of this $typeSingular", $type);
	}
	/**
	 * @param string $message
	 * @param string|null $redirectionRoute
	 * @return JsonResponse|RedirectResponse
     */
	private function getFailedAppUpdateResponse(string $message, string $redirectionRoute = null){
		$successOrError = 'error';
		if(!$message){
			$message = Lang::get($this->getAppTypeFromRoute() . '/message.error.update');
		}
		$statusCode = 400;
		if(!$redirectionRoute){
			$redirectionRoute = 'update/' . Str::singular($this->getAppTypeFromRoute());
		}
		QMLog::error($message);
		$params = [];
		$id = $this->getClientOrAppId();
		if(is_int($id)){
			$params[Application::FIELD_ID] = $id;
		} else{
			$params[Application::FIELD_CLIENT_ID] = $id;
		}
		return $this->getResponse($redirectionRoute, $params, [], $successOrError, $message, $statusCode);
	}
	/**
	 * @return mixed
	 */
	private function getClientOrAppId(): ?string{
		if($id = $this->getClientOrAppIdFromRequest()){
			return $id;
		}
		if(isset($this->clientOrAppId)){
			return $this->clientOrAppId;
		} else{
			logError("this->clientOrAppId not set!");
		}
		return null;
	}

    /**
     * @param $application
     * @throws SecretException
     * @throws MimeTypeNotAllowed
     */
	public function saveImages($application){
		if(is_string($application->app_design)){
			$application->app_design = json_decode($application->app_design);
		}
		if($file = $this->getRequest()->file('icon_url')){
			$application->icon_url = S3Images::upload($application->icon_url, $file->getContent());
		}
		if($file = $this->getRequest()->file('splash_screen')){
			$application->splash_screen = S3Images::upload($application->splash_screen, $file->getContent());
		}
		if($file = $this->getRequest()->file('text_logo')){
			$application->text_logo = S3Images::upload($application->text_logo, $file->getContent());
		}
	}
	/**
	 * Delete confirmation for the given app.
	 * @param int|null $id
	 * @return \Illuminate\Contracts\View\View
	 */
	public function getModalDelete(int $id = null): \Illuminate\Contracts\View\View{
		$model = $this->getAppTypeFromRoute();
		$confirm_route = $error = null;
		if($app = Application::findInMemoryOrDB($id)){
			$confirm_route = route('delete/' . Str::singular($model), ['id' => $app->id]);
			return View::make('layouts/modal_confirmation', compact('error', 'model', 'confirm_route'));
		}
		$error = Lang::get('admin/apps/message.app_not_found', compact('id'));
		return View::make('layouts/modal_confirmation', compact('error', 'model', 'confirm_route'));
	}
	/**
	 * Delete clients and endpoints from both laravel and old oauth tables
	 * @param $clientId
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deleteClients($clientId){
		OAClient::destroy($clientId);
		OAClient::where('client_id', $clientId)->delete();
	}
	/**
	 * Delete collaborators associated with the application
	 * @param $clientOrAppId
	 * @return JsonResponse
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deleteCollaborators($clientOrAppId): JsonResponse{
		if(is_numeric($clientOrAppId) && $clientOrAppId > 0){
			$success = Collaborator::where('app_id', $clientOrAppId)->delete();
		} else{
			$success = Collaborator::where('client_id', $clientOrAppId)->delete();
		}
		return new JsonResponse(['success' => $success]);
	}
	/**
	 * Delete the given app.
	 * @param null $clientOrAppId
	 * @return RedirectResponse|Response|ResponseFactory
	 * @throws ClientNotFoundException
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public function getDelete($clientOrAppId = null){
		if($application = $this->getApp($clientOrAppId)){
			$this->deleteClients($application->client_id);
			$this->deleteCollaborators($application->id);
			$application->delete();
			return $this->getSuccessfulAppUpdateResponse(Lang::get($this->getAppTypeFromRoute() .
				'/message.success.delete'), 204);
		}
		return $this->getAppNotFoundResponse();
	}
	/**
	 * @param $clientOrAppId
	 * @return Application|null
	 */
	private function getApp($clientOrAppId): ?Application{
		if($clientOrAppId){
			$this->clientOrAppId = $clientOrAppId;
		}
		if($this->application){
			return $this->application;
		}
		return $this->application = Application::findByClientOrAppId($clientOrAppId);
	}
	/**
	 * @param $clientOrAppId
	 * @return JsonResponse
	 * @throws TooManyEmailsException
	 */
	public function postAddCollaborator($clientOrAppId): JsonResponse{
		$inputs = $this->getRequest()->all();
		$rules = ['email' => 'required|email'];
		$response = ['success' => false];
		$validator = Validator::make($inputs, $rules);
		if($validator->fails()){
			$errors = $validator->errors()->toArray();
			$response['message'] = implode(' ', $errors['email']);
			return new JsonResponse($response);
		}
		$application = $this->getApp($clientOrAppId);
		$mail = new CollaboratorInvitationEmail($inputs['email'], $application->client_id);
		$mail->sendMe();
		$user = $mail->getUser();
		try {
			$collaborator = Collaborator::create([
				'app_id' => $application->id,
				'user_id' => $user->getId(),
				'type' => 'collaborator',
				'client_id' => $application->client_id,
			]);
			return new JsonResponse([
				'success' => true,
				'message' => 'User added as a collaborator',
				'avatar' => $collaborator->getUser()->avatar_image,
				'email' => $collaborator->getUser()->user_email,
				'collaborator' => $collaborator->id,
			]);
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			return new JsonResponse([
				'success' => false,
				'message' => "This user $user is already a collaborator",
			]);
		}
	}
	/**
	 * @return JsonResponse
	 */
	public function postDeleteCollaborator(): JsonResponse{
		$inputs = $this->getRequest()->all();
		if(empty($inputs['id'])){
			return new JsonResponse([
				'success' => false,
				'message' => 'Something went horribly wrong! Please create a support ticket at http://help.quantimo.do.',
			]);
		}
		DB::table('collaborators')->where('id', $inputs['id'])->delete();
		return new JsonResponse(['success' => true]);
	}
	/**
	 * @param $clientId
	 * @return JsonResponse
	 * @throws ClientNotFoundException
	 * @throws Exception
	 */
	public function postDeleteCollaboratorByUserId($clientId): JsonResponse{
		$u = QMAuth::getQMUser();
		/** @var AppSettings $as */
		$as = AppSettings::find($clientId);
		if(!$as->isOwner($u)){
			throw new UnauthorizedException("Only the creator of an app can delete collaborators!");
		}
		$inputs = $this->getRequest()->all();
		$userId = BaseUserIdProperty::pluck($inputs);
		if(!$userId){throw new BadRequestException("Please provide user id");}
		$qb = Collaborator::whereAppId($as->getId())
			->where(Collaborator::FIELD_USER_ID, $userId);
		$exists = $qb->first();
		if(!$exists){throw new BadRequestException("user id $userId is not a collaborator of $as");}
		return new JsonResponse([
			'success' => $qb->delete(),
		], 204);
	}
	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function postShareUserData(Request $request): JsonResponse{
		$patient = QMAuth::getQMUser();
		$response = $patient->shareData($request->get('physician_email'));
		return new JsonResponse($response, 201);
	}
	/**
	 * @return \Illuminate\Support\Collection
	 */
	private function getAppRowsForCurrentUser(): Collection{
		$user = Auth::user();
		if(!$user){
			le("No user found");
		}
		$qb = DB::table('applications')
		        ->join('collaborators', 'collaborators.app_id', '=', 'applications.id')
		        ->orderBy('app_display_name', 'asc')
		        ->where('study', $this->routeIsStudy())
		        ->where('collaborators.user_id', $user->ID)
		        ->where('physician', $this->routeIsPhysician());
		$apps = $qb->get([
			'applications.id',
			'applications.app_display_name',
			'applications.user_id',
			'applications.client_id',
		]);
		return $apps;
	}
	/**
	 * @param int $userId
	 * @return \Illuminate\Support\Collection
	 */
	public static function getPhysicianAppRowsForUser(int $userId): Collection{
		$qb = Application::whereUserId($userId)
			->where('physician', 1);
		$apps = $qb->get([
			'applications.id',
			'applications.app_display_name',
			'applications.user_id',
			'applications.client_id',
		]);
		if($apps->count() > 1){
			/** @var Application $match */
			$match = $apps->filter(function($one) use ($userId){
				/** @var Application $one */
				$user = QMUser::find($userId);
				return stripos($one->client_id, $user->getLoginName()) !== false;
			})->first();
			QMLog::error($apps->count() . " physician apps! Using " . $match->app_display_name);
		}
		return $apps;
	}

}
