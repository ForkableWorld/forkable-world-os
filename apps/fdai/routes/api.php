<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
// AUTH
use App\Http\Controllers\API;
use App\Http\Controllers\API\TrackingReminderNotificationAPIController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatGPT;
use App\Http\Controllers\DataGemController;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LifeForceController;
use App\Http\Controllers\StaticDataController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\QMAuthenticate;
use App\Slim\Controller\Connector\GetConnectorsController;
use App\Slim\Controller\TrackingReminder\GetTrackingReminderNotificationController;
use App\Slim\Controller\TrackingReminder\PostTrackingReminderController;
use App\Slim\Controller\User\PostUserSettingsController;
use App\Slim\Controller\Variable\SearchVariableController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->middleware(AdminMiddleware::NAME)->group(function () {
	Route::get('tracking_reminder_notifications/generate',
	           TrackingReminderNotificationAPIController::class . '@generate');
	Route::get('tracking_reminder_notifications/notify',
	           TrackingReminderNotificationAPIController::class . '@notify');
});



Route::prefix('v3')->group(function () {
    Route::delete('client/delete', \App\Slim\Controller\AppSettings\DeleteClientAppController::class . '@delete');
//    Route::delete('connectors/:connector/:method', \App\Slim\Controller\Connector\ConnectorController::class . '@delete');
    Route::delete('measurements/delete', \App\Slim\Controller\DeleteMethods\Measurement\DeleteMeasurementController::class . '@delete');
	Route::delete('trackingReminders/delete', \App\Slim\Controller\DeleteMethods\TrackingReminder\DeleteTrackingReminderController::class . '@delete');
	Route::delete('user/delete', \App\Slim\Controller\User\DeleteUserController::class . '@delete');
	Route::delete('userTags/delete', \App\Slim\Controller\DeleteMethods\UserTag\DeleteUserTagController::class . '@delete');
	Route::delete('userTags/ingredient/delete', \App\Slim\Controller\DeleteMethods\UserTag\DeleteIngredientUserTagController::class . '@delete');
	Route::delete('userTags/parent/delete', \App\Slim\Controller\DeleteMethods\UserTag\DeleteParentUserTagController::class . '@delete');
	Route::delete('userVariables/delete', \App\Slim\Controller\DeleteMethods\UserVariable\DeleteUserVariableController::class . '@delete');
	Route::delete('variables/join/delete', \App\Slim\Controller\DeleteMethods\UserTag\DeleteJoinUserTagController::class . '@delete');
	Route::delete('variables/user/delete', \App\Slim\Controller\DeleteMethods\UserVariable\DeleteUserVariableController::class . '@delete');
	Route::delete('votes/delete', \App\Slim\Controller\DeleteMethods\Vote\DeleteVoteController::class . '@delete');
	Route::get('activities', \App\Slim\Controller\Activity\GetActivityController::class . '@get');
	Route::get('aggregatedCorrelations', \App\Slim\Controller\Correlation\GetAggregatedCorrelationController::class . '@get');
	Route::get('apiStats', \App\Slim\Controller\GetApiStatsController::class . '@get');
	Route::get('appSettings', \App\Slim\Controller\AppSettings\GetAppSettingsController::class . '@get');
	Route::get('card', \App\Slim\Controller\Card\GetCardController::class . '@get');
	//Route::get('chart/:chartType', \App\Slim\Controller\GetChartPageController::class . '@get');
	Route::get('connect.js', \App\Slim\Controller\Connector\IntegrationJsController::class . '@get');
	Route::get('connect/mobile', \App\Slim\Controller\Connector\IntegrationJsController::class . '@get');
	Route::get('connection/finish', \App\Slim\Controller\Connector\ConnectionFinishController::class . '@get');
//	Route::get('connectors', \App\Slim\Controller\Connector\GetConnectorsController::class . '@get');
//	Route::get('connectors/:connector/:method', \App\Slim\Controller\Connector\ConnectorController::class . '@get');
	Route::get('connectors/finish', \App\Slim\Controller\Connector\ConnectionFinishController::class . '@get');
	Route::get('connectors/list', \App\Slim\Controller\Connector\GetConnectorsController::class . '@get');
	Route::get('connectors/list', GetConnectorsController::class . '@get');
	Route::get('user_variable_relationships', \App\Slim\Controller\Correlation\GetCorrelationController::class . '@get');
	Route::get('user_variable_relationships/explanations', \App\Slim\Controller\Correlation\GetCorrelationExplanationsController::class . '@get');
	Route::get('facebookMessage', \App\Slim\Controller\User\GetFacebookMessageController::class . '@get');
	Route::get('feed', \App\Slim\Controller\Feed\GetUserFeedController::class . '@get');
	Route::get('highcharts', \App\Slim\Controller\GetHighchartController::class . '@get');
	Route::get('integration.js', \App\Slim\Controller\Connector\IntegrationJsController::class . '@get');
	Route::get('integration/mobile', \App\Slim\Controller\Connector\IntegrationJsController::class . '@get');
	Route::get('measurements', \App\Slim\Controller\Measurement\GetMeasurementController::class . '@get');
	Route::get('measurements/csv', \App\Slim\Controller\Measurement\GetMeasurementCsvController::class . '@get');
	Route::get('measurements/daily', \App\Slim\Controller\Measurement\GetDailyMeasurementController::class . '@get');
	Route::get('measurements/delete', \App\Slim\Controller\Measurement\GetDeleteMeasurementController::class . '@get');
	//Route::get('measurementSources', \App\Slim\Controller\MeasurementSource\ListMeasurementSourceController::class .
	                                  //'@get');
	Route::get('measurementsRange', \App\Slim\Controller\MeasurementRange\GetMeasurementRangeController::class . '@get');
	Route::get('notes', \App\Slim\Controller\Measurement\GetNotesController::class . '@get');
	Route::get('notificationPreferences', \App\Slim\Controller\User\GetNotificationPreferencesController::class . '@get');
	Route::get('oauth/authorize', \App\Slim\Controller\OAuth2\GetAuthorizationPageController::class . '@get');
	Route::get('oauth2/authorize', \App\Slim\Controller\OAuth2\GetAuthorizationPageController::class . '@get');
	Route::get('pairs', \App\Slim\Controller\Pair\GetPairController::class . '@get');
	Route::get('pairs', \App\Slim\Controller\Pair\GetPairController::class . '@get');
	Route::get('pairsCsv', \App\Slim\Controller\Pair\GetPairCsvController::class . '@get');
	Route::get('public/user_variable_relationships/search/:search', \App\Slim\Controller\Correlation\GetAggregatedCorrelationController::class . '@get');
	Route::get('public/variables', \App\Slim\Controller\Variable\GetCommonVariableController::class . '@get');
	Route::get('public/variables/search/:search', \App\Slim\Controller\Variable\SearchPublicVariableController::class . '@get');
	Route::get('report', \App\Slim\Controller\Report\GetReportController::class . '@get');
	Route::get('shares', \App\Slim\Controller\Share\GetSharesController::class . '@get');
	Route::get('sql', \App\Slim\Controller\GetSQLController::class . '@get');
	Route::get('static', \App\Slim\Controller\StaticData\GetStaticDataController::class . '@get');
	Route::get('static/:s3Path+', \App\Slim\Controller\StaticData\GetStaticDataController::class . '@get');
	Route::get('studies', \App\Slim\Controller\Study\GetStudiesController::class . '@get');
	Route::get('studies/created', \App\Slim\Controller\Study\GetStudiesController::class . '@get');
	Route::get('studies/joined', \App\Slim\Controller\Study\GetStudiesController::class . '@get');
	Route::get('studies/open', \App\Slim\Controller\Study\GetStudiesController::class . '@get');
	Route::get('study', \App\Slim\Controller\Study\GetStudyController::class . '@get');
	Route::get('study/html', \App\Slim\Controller\Study\GetStudyHtmlController::class . '@get');
	Route::get('study/population', \App\Slim\Controller\Study\GetStudyController::class . '@get');
	Route::get('study/user', \App\Slim\Controller\Study\GetStudyController::class . '@get');
	Route::get('test', \App\Slim\Controller\Test\GetTestController::class . '@get');
	Route::get('trackingReminderNotifications', \App\Slim\Controller\TrackingReminder\GetTrackingReminderNotificationController::class . '@get');
	Route::get('trackingReminderNotifications', GetTrackingReminderNotificationController::class . '@get');
	Route::get('trackingReminderNotifications/future', \App\Slim\Controller\TrackingReminder\GetFutureTrackingReminderNotificationController::class . '@get');
	Route::get('trackingReminderNotifications/past', \App\Slim\Controller\TrackingReminder\GetPastTrackingReminderNotificationController::class . '@get');
	Route::get('trackingReminders', \App\Slim\Controller\TrackingReminder\GetTrackingReminderController::class . '@get');
	Route::get('unitCategories', \App\Slim\Controller\UnitCategory\GetUnitCategoriesController::class . '@get');
	Route::get('units', \App\Slim\Controller\Unit\GetUnitsController::class . '@get');
	Route::get('unitsVariable', \App\Slim\Controller\Unit\ListUnitForVariableController::class . '@get');
	Route::get('user', \App\Slim\Controller\User\GetAuthenticatedUserController::class . '@get')
	     ->middleware(QMAuthenticate::NAME);
	Route::get('user/me', \App\Slim\Controller\User\GetAuthenticatedUserController::class . '@get');
	Route::get('user/variables', \App\Slim\Controller\Variable\GetVariablesController::class . '@get');
	Route::get('users', \App\Slim\Controller\User\GetUsersController::class . '@get');
	Route::get('userTags', \App\Slim\Controller\UserTag\GetUserTagController::class . '@get');
	Route::get('userTags/delete', \App\Slim\Controller\UserTag\GetDeleteUserTagController::class . '@get');
	Route::get('userVariables', \App\Slim\Controller\Variable\GetVariablesController::class . '@get');
	Route::get('variableCategories', \App\Slim\Controller\VariableCategory\GetVariableCategoryController::class . '@get');
	Route::get('variables', \App\Slim\Controller\Variable\GetVariablesController::class . '@get');
	Route::get('variables/:variableName/causes', \App\Slim\Controller\Correlation\GetUserVariableRelationshipController::class . '@get');
	Route::get('variables/:variableName/effects', \App\Slim\Controller\Correlation\GetUserVariableRelationshipController::class . '@get');
	Route::get('variables/:variableName/public/causes', \App\Slim\Controller\Correlation\GetAggregatedCorrelationController::class . '@get');
	Route::get('variables/:variableName/public/effects', \App\Slim\Controller\Correlation\GetAggregatedCorrelationController::class . '@get');
	Route::get('variables/common', \App\Slim\Controller\Variable\GetCommonVariableController::class . '@get');
	Route::get('variables/delete', \App\Slim\Controller\Variable\GetDeleteVariableController::class . '@get');
	Route::get('variables/search/:search', \App\Slim\Controller\Variable\SearchVariableController::class . '@get');
	Route::get('variables/user', \App\Slim\Controller\Variable\GetVariablesController::class . '@get');
	Route::get('vote', \App\Slim\Controller\Vote\GetVoteController::class . '@get');
	Route::get('votes', \App\Slim\Controller\Vote\GetVotesController::class . '@get');
	Route::get('window/close', \App\Slim\Controller\Connector\WindowCloseController::class . '@get');
//	Route::options('oauth/access_token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@options');
//	Route::options('oauth/token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@options');
//	Route::options('oauth2/access_token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@options');
//	Route::options('oauth2/token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@options');
	Route::post('activities', \App\Slim\Controller\Activity\PostActivityController::class . '@post');
	Route::post('appSettings', \App\Slim\Controller\AppSettings\PostAppSettingsController::class . '@post');
	Route::post('connect/tokens', \App\Slim\Controller\Connector\PostConnectTokensController::class . '@post');
	Route::post('connection/publicToken', \App\Slim\Controller\Connector\PublicTokenController::class . '@post');
//	Route::post('connectors/:connector/:method', \App\Slim\Controller\Connector\ConnectorController::class . '@post');
	Route::post('connectors/connect', \App\Slim\Controller\Connector\CreateConnectionController::class . '@post');
	Route::post('user_variable_relationships', \App\Slim\Controller\Correlation\PostCorrelationController::class . '@post');
	Route::post('deviceTokens', \App\Slim\Controller\DeviceToken\PostDeviceTokenController::class . '@post');
	Route::post('deviceTokens/delete', \App\Slim\Controller\DeviceToken\DeleteDeviceTokenController::class . '@post');
	Route::post('dialogflow', \App\Slim\Controller\DialogFlowController::class . '@post');
	Route::post('email', \App\Slim\Controller\User\SendEmailController::class . '@post');
	Route::post('facebookMessage', \App\Slim\Controller\User\PostFacebookMessageController::class . '@post');
	Route::post('feed', \App\Slim\Controller\Feed\PostUserFeedController::class . '@post');
	Route::post('googleIdToken', \App\Slim\Controller\User\GoogleIdTokenController::class . '@post');
	Route::post('measurements', \App\Slim\Controller\Measurement\PostMeasurementController::class . '@post');
	Route::post('measurements', \App\Slim\Controller\Measurement\PostMeasurementController::class . '@post');
	Route::post('measurements/delete', \App\Slim\Controller\Measurement\DeleteMeasurementController::class . '@post');
	Route::post('measurements/post', \App\Slim\Controller\Measurement\PostMeasurementController::class . '@post');
	Route::post('measurements/update', \App\Slim\Controller\Measurement\UpdateMeasurementController::class . '@post');
	Route::post('measurements/v2', \App\Slim\Controller\Measurement\PostMeasurementController::class . '@post');
	Route::post('measurementSources', \App\Slim\Controller\MeasurementSource\CreateMeasurementSourceController::class . '@post');
//	Route::post('oauth/access_token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@post');
//	Route::post('oauth/authorize', \App\Slim\Controller\OAuth2\CreateAuthorizationTokenController::class . '@post');
//	Route::post('oauth/token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@post');
//	Route::post('oauth2/access_token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@post');
	Route::post('oauth2/authorize', \App\Slim\Controller\OAuth2\CreateAuthorizationTokenController::class . '@post');
//	Route::post('oauth2/token', \App\Slim\Controller\OAuth2\CreateAccessTokenController::class . '@post');
	Route::post('quantimodo/connect/finish', \App\Slim\Controller\Connector\QuantiModoConnectFinishController::class . '@post');
	Route::post('recast', \App\Slim\Controller\RecastController::class . '@post');
	Route::post('recast/errors', \App\Slim\Controller\RecastController::class . '@post');
	Route::post('sendGridEventNotification', \App\Slim\Controller\User\SendGridController::class . '@post');
	Route::post('shares', \App\Slim\Controller\Share\PostShareController::class . '@post');
	Route::post('shares/delete', \App\Slim\Controller\Share\DeleteShareController::class . '@post');
	Route::post('shares/invite', \App\Slim\Controller\Share\PostShareController::class . '@post');
	Route::post('shares/invitePatient', \App\Slim\Controller\Share\PostPatientInvitationController::class . '@post');
	Route::post('study', \App\Slim\Controller\Study\PublishStudyController::class . '@post');
	Route::post('study/create', \App\Slim\Controller\Study\CreateStudyController::class . '@post');
	Route::post('study/join', \App\Slim\Controller\Study\JoinStudyController::class . '@post');
	Route::post('study/publish', \App\Slim\Controller\Study\PublishStudyController::class . '@post');
	Route::post('trackingReminderNotification/received', \App\Slim\Controller\TrackingReminder\ReceivedTrackingReminderNotificationsController::class . '@post');
	Route::post('trackingReminderNotifications', \App\Slim\Controller\TrackingReminder\PostTrackingReminderNotificationsController::class . '@post');
	Route::post('trackingReminderNotifications/skip', \App\Slim\Controller\TrackingReminder\SkipTrackingReminderNotificationController::class . '@post');
	Route::post('trackingReminderNotifications/skip/all', \App\Slim\Controller\TrackingReminder\SkipAllTrackingReminderNotificationsController::class . '@post');
	Route::post('trackingReminderNotifications/snooze', \App\Slim\Controller\TrackingReminder\SnoozeTrackingReminderNotificationController::class . '@post');
	Route::post('trackingReminderNotifications/track', \App\Slim\Controller\TrackingReminder\TrackTrackingReminderNotificationController::class . '@post');
	Route::post('trackingReminders', \App\Slim\Controller\TrackingReminder\PostTrackingReminderController::class . '@post');
	Route::post('trackingReminders', PostTrackingReminderController::class . '@post');
	Route::post('trackingReminders/delete', \App\Slim\Controller\TrackingReminder\DeleteTrackingReminderController::class . '@post');
	Route::post('upgrade', \App\Slim\Controller\AppSettings\PostUpgradeController::class . '@post');
	Route::post('user', \App\Slim\Controller\User\PostUserController::class . '@post');
	Route::post('userSettings', \App\Slim\Controller\User\PostUserSettingsController::class . '@post');
	Route::post('userTags', \App\Slim\Controller\UserTag\PostUserTagController::class . '@post');
	Route::post('userTags/delete', \App\Slim\Controller\UserTag\DeleteUserTagController::class . '@post');
	Route::post('userTags/ingredient', \App\Slim\Controller\UserTag\CreateIngredientUserTagController::class . '@post');
	Route::post('userTags/ingredient/delete', \App\Slim\Controller\UserTag\DeleteIngredientUserTagController::class . '@post');
	Route::post('userTags/parent', \App\Slim\Controller\UserTag\CreateParentUserTagController::class . '@post');
	Route::post('userTags/parent/delete', \App\Slim\Controller\UserTag\DeleteParentUserTagController::class . '@post');
	Route::post('userVariables', \App\Slim\Controller\UserVariable\PostUserVariableController::class . '@post');
	Route::post('userVariables/delete', \App\Slim\Controller\UserVariable\DeleteUserVariableController::class . '@post');
	Route::post('userVariables/reset', \App\Slim\Controller\UserVariable\ResetUserVariableController::class . '@post');
	Route::post('variables', \App\Slim\Controller\Variable\PostVariableController::class . '@post');
	Route::post('variables/join', \App\Slim\Controller\UserTag\CreateJoinUserTagController::class . '@post');
	Route::post('variables/join/delete', \App\Slim\Controller\UserTag\DeleteJoinUserTagController::class . '@post');
	Route::post('variables/user', \App\Slim\Controller\UserVariable\PostUserVariableController::class . '@post');
	Route::post('variables/user/delete', \App\Slim\Controller\UserVariable\DeleteUserVariableController::class . '@post');
	Route::post('variables/user/reset', \App\Slim\Controller\UserVariable\ResetUserVariableController::class . '@post');
	Route::post('variableUserSettings', \App\Slim\Controller\UserVariable\PostUserVariableController::class . '@post');
	Route::post('votes', \App\Slim\Controller\Vote\PostVoteController::class . '@post');
	Route::post('votes/delete', \App\Slim\Controller\Vote\DeleteVoteController::class . '@post');

});

Route::prefix('v6')->group(function () {
    //Route::apiResource('purchases', 'API\PurchaseAPIController');
    //Route::apiResource('subscriptions', 'API\SubscriptionAPIController');
    Route::apiResource('global_variable_relationships', API\GlobalVariableRelationshipAPIController::class);
    //Route::apiResource('analyze', API\UserStudyAPIController::class);
    Route::apiResource('applications', API\ApplicationAPIController::class);
    Route::apiResource('collaborators', API\CollaboratorAPIController::class);
    Route::apiResource('common_tags', API\CommonTagAPIController::class);
	Route::apiResource('digital_twin', DigitalTwinController::class);
	Route::apiResource('data_gems', DataGemController::class);
    Route::apiResource('connections', API\ConnectionAPIController::class);
    Route::apiResource('connector_imports', API\ConnectorImportAPIController::class);
    Route::apiResource('connector_requests', API\ConnectorRequestAPIController::class);
    Route::apiResource('connectors', API\ConnectorAPIController::class);
    Route::apiResource('user_variable_relationships', API\CorrelationAPIController::class);
    Route::apiResource('device_tokens', API\DeviceTokenAPIController::class);
    Route::get('feed', FeedController::class . '@get');
	Route::post('feed', FeedController::class . '@store');
    Route::apiResource('measurement_exports', API\MeasurementExportAPIController::class);
    Route::apiResource('measurement_imports', API\MeasurementImportAPIController::class);
    Route::apiResource('measurements', API\MeasurementAPIController::class);
    Route::apiResource('notifications', API\NotificationAPIController::class)
	    ->middleware(QMAuthenticate::class);
    Route::apiResource('oa_access_tokens', API\OAAccessTokenAPIController::class);
    Route::apiResource('oa_clients', API\OAClientAPIController::class);
    Route::apiResource('sent_emails', API\SentEmailAPIController::class);
    Route::get('static', StaticDataController::class."@index");
    Route::apiResource('studies', API\StudyAPIController::class);
    Route::apiResource('tracking_reminder_notifications', API\TrackingReminderNotificationAPIController::class);
    Route::apiResource('tracking_reminders', API\TrackingReminderAPIController::class);
    Route::apiResource('unit_categories', API\UnitCategoryAPIController::class);
    Route::apiResource('units', API\UnitAPIController::class);
    Route::apiResource('user_studies', API\UserStudyAPIController::class);
    Route::apiResource('user_tags', API\UserTagAPIController::class);
    Route::apiResource('user_variables', API\UserVariableAPIController::class);
    Route::apiResource('users', API\UserAPIController::class);
	Route::get('users/metadata/{ethAddress}', API\UserAPIController::class. '@metadata');
	Route::get('me', API\UserAPIController::class . '@me');
	Route::post('me', API\UserAPIController::class . '@me');
	Route::get('analyze/user', API\UserAPIController::class . '@analyze');
	Route::post('analyze/user', API\UserAPIController::class . '@analyze');
	Route::apiResource('user_meta', API\UserMetaAPIController::class);
    Route::apiResource('variable_categories', API\VariableCategoryAPIController::class);
    Route::apiResource('variables', API\VariableAPIController::class);
    Route::apiResource('votes', API\VoteAPIController::class);
    Route::apiResource('wp_posts', API\WpPostAPIController::class);
    Route::get('logout', [LoginController::class, 'getLogout']);
    Route::get('variables/search/{q}', SearchVariableController::class . '@get');
    Route::post('login', [LoginController::class, 'getLogin']);
    Route::post('register', [RegisterController::class, 'getRegister']);
    Route::post('shares', \App\Slim\Controller\Share\PostShareController::class . '@post');
    Route::post('userSettings', PostUserSettingsController::class . '@post');
	Route::prefix('chat')->group(function () {
		Route::post('/', ChatGPT::class . '@post');
		Route::delete('/', ChatGPT::class . '@delete');
		Route::post('send-message', ChatGPT::class . '@sendMessage');
		Route::get('event-stream', ChatGPT::class . '@eventStream');
	});
	Route::get('lifeForce', LifeForceController::class . '@metadata');
});
// ADMIN
//Route::group(['prefix' => 'api/v6', 'middleware' => ['admin', 'jsonify']], function () {
//    //Route::post('units', 'API\UnitAPIController');
//});

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});
