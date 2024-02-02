<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Auth;
use Illuminate\Support\Arr;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\Factory as View;
class OauthController extends Controller {
	public function getAuthorizationForm(Authorizer $authorizer, View $view, Guard $auth, Request $request){
		// display a form where the user can authorize the client to access it's data
		$authParams = $authorizer->getAuthCodeRequestParams();
		$scopes = array_keys($authParams['scopes']);
		$formParams = Arr::except($authParams, 'client');
		$formParams['client_id'] = $authParams['client']->getId();
		$formParams['scope'] = implode(config('oauth2.scope_delimiter'), array_map(function($scope){
			return $scope->getId();
		}, $authParams['scopes']));
		/** @var User $user */
		$user = Auth::user();
		return $view->make('oauth.authorization-form', [
			'route' => '',
			'params' => $formParams,
			'client' => $authParams['client'],
			'scopes' => $scopes,
			'loginName' => $user->user_login,
			'requestPath' => $request->getRequestUri(),
		]);
	}
	public function getAuthorizationCode(Request $request, Authorizer $authorizer, Guard $auth, Redirector $redirect){
		$params = $authorizer->getAuthCodeRequestParams();
		/** @var User $user */
		$user = Auth::user();
		$params['user_id'] = $user->getId();
		$redirectUri = '';
		// if the user has allowed the client to access its data, redirect back to the client with an auth code
		if($request->get('approve') !== null){
			$redirectUri = $authorizer->issueAuthCode('user', $params['user_id'], $params);
		}
		// if the user has denied the client to access its data, redirect back to the client with an error message
		if($request->get('deny') !== null){
			$redirectUri = $authorizer->authCodeRequestDeniedRedirectUri();
		}
		return $redirect->to($redirectUri);
	}
	public function getAccessToken(Authorizer $authorizer, ResponseFactory $response){
		$accessToken = $authorizer->issueAccessToken();
		return $response->json($accessToken);
	}
}
