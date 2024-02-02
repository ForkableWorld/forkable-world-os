<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use Illuminate\Support\Str;
use App\Exceptions\ExceptionHandler;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use Carbon\Carbon;
use Illuminate\Container\Container;
class OauthService {
    public $clientId = null;
    public function __construct(Container $container){
        $this->container = $container;
    }
    /**
     * @param null $redirectUris
     * @return bool
     */
    public function redirectsValid($redirectUris = null): bool{
        if(!empty($redirectUris)){
            $redirectUris = preg_split('/\r\n|[\r\n]/', $redirectUris);
            $redirectUris = array_unique($redirectUris);
            foreach($redirectUris as $uri){
                if(!empty($uri) && $this->isUriLocalHostOrHttps($uri)){
                    $uri = trim($uri);
                    $uris[] = $uri;
                }else{
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * @param $uri
     * @return bool
     */
    public function isUriLocalHostOrHttps($uri): bool{
        return strpos($uri, 'https://') !== false || strpos($uri, 'localhost') !== false;
    }
    /**
     * @param [] $appData
     * @param User $user
     * @param string $clientId
     * @return Application
     */
    public function createClientApplication($data, $user, $clientId): Application{
        $clientId = BaseClientIdProperty::sanitize($clientId);
        if($app = Application::whereClientId($clientId)->first()){return $app;}
        $data['user_id'] = $user->getId();
        $data['client_id'] = $clientId;
        if(!isset($data['app_display_name'])){$data['app_display_name'] = $user->display_name;}
        $app = new Application($data);
        $this->createClients($app->client_id, $user, $app);
        try {
            $app->saveOrFail();
        } catch (\Throwable $e) {
            le($e);
        }
        Collaborator::create([
            'app_id'    => $app->id,
            'user_id'   => $user->getId(),
            'type'      => 'owner',
            'client_id' => $app->client_id
        ]);
        return $app;
    }
    /**
     * @param [] $appData
     * @param User $user
     * @return Application
     */
    public function createStudyApplication($appData, $user): Application{
        $appData['study'] = 1;
        $appData['billing_enabled'] = 0;
        return $this->createClientApplication($appData, $user, Str::random(16));
    }
    /**
     * @param User $user
     * @return Application
     */
    public function createPhysicianApplication($user): Application{
        $appData = [
            'app_display_name' => $user->display_name,
            'plan_id'          => 0,
            'homepage_url'     => $user->avatar_image,
            'physician'        => 1,
            'billing_enabled'  => 0
        ];
        return $this->createClientApplication($appData, $user, $user->user_email);
    }
    /**
     * @param string $clientId
     * @param \App\Models\User $user
     * @param Application $application
     * @param array|string $redirectUris
     * @return bool
     */
    public function createClients($clientId, $user, $application = null, $redirectUris = []): bool{
        if(!$application || $application->physician == 1){
            $redirectUris[] = getHostAppSettings()->additionalSettings->downloadLinks->webApp."/#/app/data-sharing";
        }elseif($application->study == 1){
            $redirectUris[] = getHostAppSettings()->additionalSettings->downloadLinks->webApp;
        }elseif(!empty($redirectUris)){
            $redirectUris = preg_split('/\r\n|[\r\n]/', $redirectUris);
            $redirectUris = array_unique($redirectUris);
            foreach($redirectUris as $uri){
                if(!empty($uri) && $this->isUriLocalHostOrHttps($uri)){
                    $uri = trim($uri);
                    $redirectUris[] = $uri;
                }
            }
        }else{
            $redirectUris[] = "https://app.quantimo.do/ionic/Modo/www/callback/";
        }
	    $client = new OAClient();
	    $client->forceFill([
            'client_id'     => $clientId,
            'client_secret' => Str::random(32),
            'user_id'       => $user->getId()
        ]);
        if(!empty($redirectUris)){
            OAClient::where('client_id', $clientId)
                ->update(['redirect_uri' => implode(' ', $redirectUris)]);
        }
		$client->save();
	    return true;
    }
    /**
     * @param string $clientId
     * @param int $limit
     * @return array|null
     */
    public static function getApplicationUserIds($clientId = null, $limit = 100): ?array{
        $userIds = null;
        if(empty($clientId)){
            return null;
        }
        $tokens = OAAccessToken::getApplicationUserAccessTokens($clientId, $limit);
        if(empty($tokens)){
            return null;
        }
        foreach($tokens as $token){
            $userIds[] = $token->user_id;
        }
        return $userIds;
    }
    /**
     * @param string $clientId
     * @param int $limit
     * @return array|null
     */
    public function getApplicationUsers($clientId = null, $limit = 100): ?array{
        $users = null;
        if(empty($clientId)){
            $clientId = $this->clientId;
        }
        if(empty($clientId)){
            return null;
        }
        $accessTokens = OAAccessToken::getApplicationUserAccessTokens($clientId, $limit);
        if(empty($accessTokens)){
            return null;
        }
        foreach($accessTokens as $token){
            $user = User::where('id', $token->user_id)->get();
            $user->accessToken = $token->access_token;
            $users[] = $user;
        }
        return $users;
    }
    /**
     * Get all the applications user authorized before and didn't expire yet
     * @param int $userId
     * @return \App\Models\OAAccessToken[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\LaravelIdea\Helper\App\Models\_IH_OAAccessToken_C|\LaravelIdea\Helper\App\Models\_IH_OAAccessToken_QB[]
     */
    public function getAuthorizedApps($userId){
        $tokens = OAAccessToken::with('application')
            ->where('user_id', $userId)
            ->where('expires', '>', Carbon::today()->toDateString())
            ->groupBy('client_id')
            ->orderBy('expires', 'desc')
            ->get();
        return $tokens;
    }
    /**
     * Delete all access tokens because user wants to revoke access to this app
     * @param int $userId
     * @param $clientId
     * @return bool|null
     */
    public function revokeAccess($userId, $clientId): ?bool{
        try {
            $deleted = OAAccessToken::where('user_id', $userId)->where('client_id', $clientId)->delete();
        } catch (\Exception $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            $deleted = false;
        }
        return $deleted;
    }
}
