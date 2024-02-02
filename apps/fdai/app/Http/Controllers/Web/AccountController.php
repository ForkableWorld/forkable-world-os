<?php /** @noinspection LaravelUnknownViewInspection */ /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */
namespace App\Http\Controllers\Web;
use App\Buttons\Auth\LogoutButton;
use App\DataSources\QMSpreadsheetImporter;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\SecretException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\User\UserUserPassProperty;
use App\Services\MeasurementService;
use App\Services\OauthService;
use App\Services\StripeService;
use App\Services\UserVariableService;
use App\Services\VariableService;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Types\QMStr;
use Auth;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\View\Factory as ViewFactory;
use Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
/** Class AccountController
 * @package App\Http\Controllers\Web
 */
class AccountController extends Controller {
	private Guard $auth;
	/**
	 * @param ViewFactory $view
	 * @param UserVariableService $userVariable
	 * @param MeasurementService $measurements
	 * @return View
	 */
	public function index(ViewFactory $view, UserVariableService $userVariable, MeasurementService $measurements): View{
		try {
			$userId = QMAuth::id();
		} catch (UnauthorizedException $e) {
			le("Auth should have been checked in middleware! \n".$e->getMessage());
		}
		$interests = $userVariable->getUserInterests($userId);
		$latestMood = $measurements->getLatestMoodMeasurement($userId);
		$latestMeasurements = $measurements->getLatestMeasurements($userId, 9);
		return $view->make('web/account/index', compact('interests', 'latestMood', 'latestMeasurements'));
	}
	/**
	 * @param ViewFactory $view
	 * @param UserVariableService $userVariable
	 * @return View
	 */
	public function edit(ViewFactory $view, UserVariableService $userVariable): View{
		try {
			$userId = QMAuth::id();
		} catch (UnauthorizedException $e) {
			le($e);
		}
		$interests = $userVariable->getUserInterests($userId);
		$accessTokenString = BaseAccessTokenProperty::fromRequest();
		if(!$accessTokenString){
			$accessTokenArray =
				QMAuth::getOrCreateAccessAndRefreshTokenArrays(getClientIdFromRequestOrQuantiModoAsFallback(), $userId);
			if($accessTokenArray){
				$accessTokenString = $accessTokenArray['accessToken'];
			}
		}
		return $view->make('web/account/edit', compact('interests', 'accessTokenString'));
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function apiExplorer(ViewFactory $view): View{
		return $view->make('web/account/apiExplorer');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function variables(ViewFactory $view): View{
		return $view->make('web/account/variables');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function connectors(ViewFactory $view): View{
		return $view->make('web/account/connectors');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function reminders(ViewFactory $view): View{
		return $view->make('web/account/reminders');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function manageReminders(ViewFactory $view): View{
		return $view->make('web/account/manage-reminders');
	}
	/**
	 * @param ValidatorFactory $validatorFactory
	 * @param Redirector $redirect
	 * @param UserVariableService $userVariable
	 * @return RedirectResponse
	 * @throws ModelValidationException
	 */
	public function postEdit(ValidatorFactory $validatorFactory, Redirector $redirect,
		UserVariableService $userVariable): RedirectResponse{
		try {
			$userId = QMAuth::id();
		} catch (UnauthorizedException $e) {
			le("Auth should have been checked in middleware! \n".$e->getMessage());
		}
		$formData = $this->getRequest()->all();
		$validator = $validatorFactory->make($formData, [
			'display_name' => 'required|max:250',
			User::FIELD_USER_LOGIN => 'required|max:60',
			'user_email' => 'required|email',
			'outcome_id' => 'integer',
			'predictor_id' => 'integer',
		]);
		if($validator->fails()){
			//return $this->goBackWithErrorMessageQueryParam($validator, $formData);
			return $redirect->back()->withInput()->withErrors($validator);
		}
		Auth::user()->fill([
			'display_name' => $formData['display_name'],
			'user_email' => $formData['user_email'],
			User::FIELD_USER_LOGIN => $formData[User::FIELD_USER_LOGIN],
			'unsubscribed' => isset($formData['unsubscribed']),
		])->save();
		if(!empty($formData['outcome_id'])){
			$userVariable->setOutcomeOfInterest($userId, $formData['outcome_id']);
		}
		if(!empty($formData['predictor_id'])){
			$userVariable->setPredictorOfInterest($userId, $formData['predictor_id']);
		}
		return $redirect->route('account')->with('success', "Your information updated successfully");
	}
	/**
	 * @param Request $request
	 * @param ResponseFactory $response
	 * @param UserVariableService $userVariable
	 * @return JsonResponse
	 */
	public function userVariableAutocomplete(Request $request, ResponseFactory $response,
		UserVariableService $userVariable): JsonResponse{
		try {
			$userId = QMAuth::id();
		} catch (UnauthorizedException $e) {
			le("Auth should have been checked in middleware! \n".$e->getMessage());
		}
		$term = $request->get('term');
		$results = $userVariable->autocompleteSearch($userId, $term);
		return $response->json($results);
	}
	/**
	 * @param Request $request
	 * @param ResponseFactory $response
	 * @param VariableService $variable
	 * @return JsonResponse
	 */
	public function publicVariableAutocomplete(Request $request, ResponseFactory $response,
		VariableService $variable): JsonResponse{
		$term = $request->get('term');
		$results = $variable->autocompleteSearch($term);
		return $response->json($results);
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function password(ViewFactory $view): View{
		return $view->make('web/account/password');
	}
	/**
	 * @param ValidatorFactory $validatorFactory
	 * @param Redirector $redirect
	 * @return $this|RedirectResponse
	 * @throws ModelValidationException
	 */
	public function postPassword(ValidatorFactory $validatorFactory, Redirector $redirect){
		$formData = $this->getRequest()->all();
		$validator = $validatorFactory->make($formData, [
			'old_password' => 'required',
			'new_password' => 'required|between:3,32',
			'confirm_new_password' => 'required|same:new_password',
		]);
		if($validator->fails()){
			//return $this->goBackWithErrorMessageQueryParam($validator, $formData);
			return $redirect->back()->withInput()->withErrors($validator);
		}
		/** @var User $user */
		$user = Auth::user();
		if(!empty($user)){
			$user->fill(['user_pass' => UserUserPassProperty::pluckEncrypted($formData)])
			     ->save();
			return $redirect->route('account')->with('success', "Your password changed successfully");
		}
		return $redirect->back()->with('error', "Please enter your current password again.");
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function updateCard(ViewFactory $view): View{
		return $view->make('web/account/update-card');
	}
	/**
	 * @return JsonResponse
	 */
	public function postUnsubscribe(): JsonResponse{
		/** @var User $u */
		$u = Auth::user();
		$provider = $u->getSubscriptionProvider();
		if(empty($provider)){
			Log::error("Unknown subscription provider!");
		}
		if(strtolower($provider) === 'google'){
			$u->setStripActive(false, $provider);
			$r['message'] =
				'You subscribed via Google Play so you must go to Google Play Store > Account > Subscriptions to cancel.';
		} elseif(strtolower($provider) === 'apple'){
			$u->setStripActive(false, $provider);
			$r['message'] =
				'You subscribed via the App Store so you must go to Settings > iTunes & App Store > Tap your Apple ID > Subscriptions to cancel.';
		} else{
			//$this->makeSureUserIsUsingStripe($user->getId());
			$r['data'] = $u->downgrade();
			$r['message'] = "User un-subscribed from QuantiModo Premium";
		}
		$r['user'] = $u->getQMUser();
		$r['success'] = true;
		return new JsonResponse($r, 201);
	}
	/**
	 * @param int $userId
	 * @return QMUser
	 */
	public function getQMUser(int $userId): QMUser{
		$user = QMUser::findWithToken($userId);
		return $user;
	}
	/**
	 * @param $userId
	 * @throws BadRequestException
	 */
	public function makeSureUserIsUsingStripe($userId){
		$user = $this->getQMUser($userId);
		if($user->getSubscriptionProvider() === 'google'){
			throw new BadRequestException('You subscribed via Google Play so you must go to Google Play Store > Account > Subscriptions to cancel.');
		}
		if($user->getSubscriptionProvider() === 'apple'){
			throw new BadRequestException('You subscribed via the App Store so you must go to Settings > iTunes & App Store > Tap your Apple ID > Subscriptions to cancel.');
		}
	}
	/**
	 * @param Guard $auth
	 * @param StripeService $stripeService
	 * @return JsonResponse
	 */
	public function postSubscribe(Guard $auth, StripeService $stripeService): JsonResponse{
		$this->auth = $auth;
		$inputs = $this->getRequest()->all();
		$user = $this->getUser();
		$subscribed = $user->subscribed();
		if($subscribed){
			$response = $this->handlePaymentUpdate($stripeService, $inputs);
		} elseif($subscribed && $this->getUser()->stripe_id){
			$response = $this->handleReUpgrade($stripeService, $inputs);
		} else{
			$response['data'] = $stripeService->createUserSubscription($inputs, $this->getUser());
			$response['message'] = "You successfully upgraded";
		}
		if(!isset($response['success'])){
			$response['success'] = true;
		}
		if(isset($response['data']['error'])){
			$response['error'] = $response['data']['error'];
			$response['success'] = false;
			QMLog::error($response['error'], $response);
			return new JsonResponse($response, 400);
		}
		$response['user'] = $response['data']['user'] ?? $this->getUser()->getQMUserArray();
		return new JsonResponse($response, 201);
	}
	/**
	 * @param Redirector $redirect
	 * @param StripeService $stripeService
	 * @return JsonResponse|RedirectResponse
	 */
	public function postUpdateCard(Redirector $redirect, StripeService $stripeService){
		$inputs = $this->getRequest()->all();
		/** @var User $user */
		$user = Auth::user();
		$update = false;
		$purchaseId = null;
		$response = [
			'message' => "User updated credit card",
			'user' => $this->getQMUser($user->getId()),
			'success' => true,
		];
		if($user->subscribed()){
			$update = $stripeService->updateCard($inputs, $user);
		} else{
			$purchaseId = $stripeService->createUserSubscription($inputs, $user);
			$response['data'] = ['purchaseId' => $purchaseId];
		}
		if($update || $purchaseId){
			if($this->weShouldReturnJsonResponse()){
				return new JsonResponse($response, 201);
			} elseif(app('request')->input('hideMenu')){
				//Session::flash('success', 'Your payment information was successfully updated');
				return $redirect->back()->with('success', 'Your payment information was successfully updated');
			} else{
				return $redirect->route('account')
					->with('success', "Your payment information was successfully updated");
			}
		}
		return $redirect->back()->with('error',
				"Your payment information could not be updated.  Please double check the info and try again.");
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function upgrade(ViewFactory $view): View{
		$type = "upgrade";
		return $view->make('web/account/change-plan', compact('type'));
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function downgrade(ViewFactory $view): View{
		$type = "downgrade";
		return $view->make('web/account/change-plan', compact('type'));
	}
	/**
	 * @param Redirector $redirect
	 * @return JsonResponse|RedirectResponse
	 */
	public function postUpgrade(Redirector $redirect){
		/** @var User $user */
		$user = Auth::user();
		if($user->subscribed()){
			try {
				$user->subscription('main')->swap(StripeService::YEARLY);
			} catch (Exception $e) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
				if($this->weShouldReturnJsonResponse()){
					throw new BadRequestException($e->getMessage());
				}
				return $redirect->back()->with('error', "Something went wrong please try later.");
			}
		}
		if($this->weShouldReturnJsonResponse()){
			$response = [
				'message' => "Successfully upgraded your plan",
				'user' => $this->getQMUser($user->getId()),
				'success' => true,
			];
			return new JsonResponse($response, 201);
		}
		return $redirect->route('account')->with('success', "Successfully upgraded your plan");
	}
	/**
	 * @param Redirector $redirect
	 * @return JsonResponse|RedirectResponse
	 */
	public function postDowngrade(Redirector $redirect){
		/** @var User $user */
		$user = Auth::user();
		if($user->subscribed()){
			try {
				$user->subscription('main')->swap(StripeService::MONTHLY);
			} catch (Exception $e) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
				if($this->weShouldReturnJsonResponse()){
					throw new BadRequestException($e->getMessage());
				}
				return $redirect->back()->with('error', "Something went wrong please try later.");
			}
		}
		if($this->weShouldReturnJsonResponse()){
			$response = [
				'message' => "Successfully downgraded your plan",
				'user' => $this->getQMUser($user->getId()),
				'success' => true,
			];
			return new JsonResponse($response, 201);
		}
		return $redirect->route('account')->with('success', "Successfully downgraded your plan");
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function exportData(ViewFactory $view): View{
		return $view->make('web/account/export-data');
	}
	/**
	 * @param MeasurementService $measurements
	 * @param $output
	 * @return JsonResponse
	 */
	public function requestExportData(MeasurementService $measurements, $output): JsonResponse{
		/** @var User $user */
		$user = Auth::user();
		$measurements->createExportRequestRecord($user, 'user', null, $output);
		$response = [
			'success' => true,
			'message' => 'You should receive your measurements within 24 hours.',
		];
		return new JsonResponse($response);
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function authorizedApps(ViewFactory $view): View{
        $clients = QMAuth::getUser()->getAuthorizedClients();
		return $view->make('web/account/applications', compact('clients'));
	}
	/**
	 * @param OauthService $oauthService
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function revokeAccess(OauthService $oauthService, Request $request): JsonResponse{
		try {
			$userId = QMAuth::id();
		} catch (UnauthorizedException $e) {
			le("Auth should have been checked in middleware! \n".$e->getMessage(), $e);
		}
		$clientId = $request->get('clientId');
		$deleted = $oauthService->revokeAccess($userId, $clientId);
		return new JsonResponse(['success' => (bool)$deleted]);
	}
	/**
	 * @param Redirector $redirect
	 * @return RedirectResponse
	 * @throws Exception
	 */
	public function deleteAccount(Redirector $redirect): RedirectResponse{
		/** @var User $user */
		$user = Auth::user();
		$user->softDeleteWithRelations("API request");
		return $redirect->to(LogoutButton::PATH)->with('success', 'Your account was deleted successfully');
	}
	/**
	 * @param Request $request
	 * @param Redirector $redirect
	 * @return RedirectResponse
	 */
	public function postSpreadsheet(Request $request, Redirector $redirect): RedirectResponse{
		$file = $request->file('file');
		if(empty($file)){
			return $redirect->route('account.edit')->with('error', "Please select a file first.");
		}
		$sourceName = $request->get('source', $request->get('sourceName', $request->get('connectorName')));
		if(!$sourceName){
			throw new BadRequestException("Please provide sourceName");
		}
		try {
			$fileUrlOnS3 = QMSpreadsheetImporter::encryptAndUploadSpreadsheetToS3(QMAuth::id(), $file, $sourceName);
		} catch (InvalidS3PathException|MimeTypeNotAllowed|SecretException|ModelValidationException|UnauthorizedException $e) {
			le($e);
		}
		if($fileUrlOnS3){
			return $redirect->route('account.edit')->with('success',
					"Your file uploaded successfully and your data should be visible in your account within the next hour.");
		}
		return $redirect->route('account.edit')->with('error', "Couldn't upload the file. Please try again.");
	}
	/**
	 * @param StripeService $stripeService
	 * @param $inputs
	 * @return array
	 */
	private function handlePaymentUpdate(StripeService $stripeService, $inputs): array{
		$response['data'] = $stripeService->updateCard($inputs, $this->getUser());
		if($response && !isset($response['data']['error'])){
			$response['message'] = "You successfully updated your payment method";
			$response['success'] = true;
		} else{
			$response['message'] = "You already have a subscription and we could not update your payment method.  " .
				QMStr::CONTACT_MIKE_FOR_HELP_STRING;
			$response['success'] = false;
		}
		return $response;
	}
	/**
	 * @return Guard
	 */
	public function getAuth(): Guard{
		return $this->auth;
	}
	/**
	 * @return User
	 */
	public function getUser(): User{
		try {
			return QMAuth::getQMUser()->l();
		} catch (UnauthorizedException $e) {
			le("Auth should have been checked in middleware! \n".$e->getMessage());
		}
		//return $this->getAuth()->user();
	}
	/**
	 * @param StripeService $stripeService
	 * @param $inputs
	 * @return array
	 */
	private function handleReUpgrade(StripeService $stripeService, $inputs): array{
		$user = $this->getUser();
		$response['data'] = $stripeService->updateCard($inputs, $user);
		if($response['data'] && !isset($data['error'])){
			$response['message'] = "You successfully re-upgraded";
		} else{
			$response['message'] =
				"You already have a Stripe account but could not re-upgrade. " . QMStr::CONTACT_MIKE_FOR_HELP_STRING;
			$response['success'] = false;
		}
		return $response;
	}
}
