<?php /** @noinspection PhpUnusedParameterInspection */
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Buttons\Auth\LoginButton;
use App\Http\Controllers\AAPanelController;
use App\Http\Controllers\API\UserStudyAPIController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatGPT;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\PinataController;
use App\Http\Controllers\RootCauseAnalysisController;
use App\Http\Controllers\Web\AppsController;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\ApiConnectorController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\BaseDataLabController;
use App\Http\Controllers\ButtonActionController;
use App\Http\Controllers\CohortStudiesController;
use App\Http\Controllers\CreateFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataLab;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\IndividualStudiesController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PopulationStudiesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicVariableController;
use App\Http\Controllers\StudiesController;
use App\Http\Controllers\UserVariablesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\V2;
use App\Http\Controllers\VariableCategoriesController;
use App\Http\Controllers\VariablesController;
use App\Http\Controllers\Web;
use App\Buttons\QMButton;
use App\Buttons\States\ImportStateButton;
use App\Buttons\States\SettingsStateButton;
use App\Buttons\States\VariableListStateButton;
use App\Charts\BarChartButton;
use App\Files\FileHelper;
use App\Files\MimeContentTypeHelper;
use App\Http\Controllers\Admin\FixInvalidRecordsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChartsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\QMAuthenticate;
use App\Mail\PhysicianInvitationEmail;
use App\Mail\PostListMail;
use App\Menus\SearchMenu;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\UserVariable;
use App\Reports\AnalyticalReport;
use App\Reports\GradeReport;
use App\Reports\RootCauseAnalysis;
use App\Repos\QMAPIRepo;
use App\Slim\APIWrappers\FoodCentralWrapper;
use App\Slim\Controller\Connector\GetConnectorsController;
use App\Slim\Controller\OAuth2\CreateAccessTokenController;
use App\Slim\Controller\OAuth2\CreateAuthorizationTokenController;
use App\Slim\Controller\OAuth2\GetAuthorizationPageController;
use App\Slim\Controller\StaticData\GetStaticDataController;
use App\Slim\Controller\Test\GetTestController;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Studies\QMStudy;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\IonicHelper;
use App\Utils\Subdomain;
use App\Utils\UrlHelper;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\EnthusiasmCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
Route::get('/', [VariablesController::class, 'index'])
	->middleware('cache.response')
     ->name('home');

Route::post('ipfs', [PinataController::class, 'post']);
Route::prefix('api/v1')->middleware(QMAuthenticate::NAME)->group(function () {
	Route::prefix('connectors')->group(function () {
		Route::get('/',[SocialAuthController::class, 'list']);
		Route::get('list',[GetConnectorsController::class, 'get']);
		Route::post('{connectorName}/connect',[SocialAuthController::class, 'connect']);
		Route::get('{connectorName}/connect',[SocialAuthController::class, 'connect']);
		Route::post('{connectorName}/disconnect',[SocialAuthController::class, 'disconnect']);
		Route::get('{connectorName}/disconnect',[SocialAuthController::class, 'disconnect']);
		Route::post('{connectorName}/update',[SocialAuthController::class, 'import']);
		Route::get('{connectorName}/update',[SocialAuthController::class, 'import']);
		Route::post('{connectorName}/import',[SocialAuthController::class, 'import']);
		Route::get('{connectorName}/import',[SocialAuthController::class, 'import']);
	});
	Route::get('user', [\App\Slim\Controller\User\GetAuthenticatedUserController::class, 'get'])
	     ->name('api.v1.user');
});
Route::get('connections/import/{id}', [ApiConnectorController::class, 'import'])
	->middleware(QMAuthenticate::NAME)
    ->name('connections.import');

// STUDIES
Route::prefix('studies')
    ->middleware('cache.response')
    ->group(function () {
        Route::get('/', [StudiesController::class, 'index'])->name('studies-index');
        Route::get('/cause/{causeNameOrId}/effect/{effectNameOrId}',
            function($causeNameOrId, $effectNameOrId){
                return GlobalVariableRelationship::findByVariableNamesOrIds($causeNameOrId, $effectNameOrId)
                    ->getShowPageView();
            })->where('causeNameOrId', '.*')  // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
            ->where('effectNameOrId', '.*');  // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
        Route::get('/{query}', [StudiesController::class, 'show'])
            ->name('studies-study');
});
Route::prefix('user-studies')->group(function () {
	Route::get('/', [StudiesController::class, 'index'])->name('user-studies-index');
	Route::get('/{query}', [StudiesController::class, 'show'])
		->name('user-study');
});
Route::get('study/{query}', [StudiesController::class, 'show'])->name('study');

Route::prefix('oauth')->group(function () {
	Route::get('authorize', [GetAuthorizationPageController::class, 'get'])
	     ->name('get.oauth.authorize')
	     ->middleware(QMAuthenticate::NAME);
	Route::post('authorize', [CreateAuthorizationTokenController::class, 'post'])
	     ->name('post.oauth.authorize')
		->middleware(QMAuthenticate::NAME);
	Route::post('token', [CreateAccessTokenController::class, 'initPost'])
	     ->name('post.oauth.token');
});

// SCIENTISTS
Route::prefix('scientists')->group(function () {
    Route::get('/', [UsersController::class, 'index'])
        ->name('users-index');
    Route::get('/{query}', [UsersController::class, 'show'])
        ->name('user');
});
Route::prefix('users')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('users');
    Route::post('{user}/follow', [UsersController::class, 'follow'])->name('follow');
    Route::delete('{user}/unfollow', [UsersController::class, 'unfollow'])->name('unfollow');
    Route::get('{userNameOrId}', function ($userNameOrId) {
        return User::findByNameIdOrSynonym($userNameOrId)
            ->getShowPageHtml();
    });
    Route::get('{userNameOrId}/variables/{$variableNameOrId}', function ($userNameOrId, $variableNameOrId) {
        User::findByNameIdOrSynonym($userNameOrId)
            ->getOrCreateUserVariable($variableNameOrId)
            ->getShowPageView();
    });
        //->where('variableNameOrId', '.*'); // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
    Route::get('{userNameOrId}/studies/cause/{causeNameOrId}/effect/{effectNameOrId}',
        function($userNameOrId, $causeNameOrId, $effectNameOrId){
            return UserVariableRelationship::findByVariableNamesOrIds($userNameOrId,
                $causeNameOrId, $effectNameOrId)
                ->getShowPage();
        })->where('causeNameOrId', '.*')
        ->where('effectNameOrId', '.*'); // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
	Route::get('{userNameOrId}/grade-report', function($userNameOrId){
		$u = User::findByNameIdOrSynonym($userNameOrId);
		$r = new GradeReport($u->getId());
		return $r->getOrGenerateHtmlWithHead();
	});
	Route::get('{userNameOrId}/root-cause-analysis/{effectNameOrId}', function($nameOrId, $effectNameOrId){
		$v = UserVariable::findByVariableIdOrName($effectNameOrId, $nameOrId);
		$v->authorizeView();
		return $v->getRootCauseAnalysis()->getOrGenerateHtmlWithHead();
	});
});
Route::prefix('demo')
	->middleware(['cache.headers:public;max_age=2628000;etag', 'cache.response'])
	->group(function () {
	    Route::get('/', function ($nameOrId) {
	        return User::mike()->getShowPageHtml();
	    });
		Route::get('grade-report', function(){
			return GradeReport::getDemoReport()->getOrGenerateHtmlWithHead();
		});
		Route::get('study', StudiesController::class."@demo");
		Route::get('root-cause-analysis', function(){
			return RootCauseAnalysis::getDemoReport()->getOrGenerateHtmlWithHead();
	});
});
Route::get('digital_twin', DigitalTwinController::class."@index")
	->middleware(QMAuthenticate::NAME);
Route::get('/me', function () {
    return redirect(SettingsStateButton::url());
});
Route::get('/mike', function () {
    return User::mike()->getShowPageView();
});
Route::prefix('menus')->middleware(QMAuthenticate::NAME)->group(function () {
	Route::get('/', function(){
		$class = QMRequest::getParam('class');
		return (new $class)->getSearchPageHtml();
	});
	Route::get('/search', function(){
		return (new SearchMenu())->getSearchPageHtml();
	});
});

Route::get('/reminders', function () {
    return redirect(VariableListStateButton::url());
});
Route::get('/settings', function () {
    return redirect(SettingsStateButton::url());
});
Route::get('/buttons', function () {
	return view('ifttt.list', ['buttons' => QMButton::all()]);
});
Route::get('images', function () {
	return redirect(SettingsStateButton::url());
});
Route::middleware([
    'cache.headers:public;max_age=2628000;etag',
    'cache.response'
])->group(function () {
	// VARIABLES
	Route::prefix('variables')->group(function () {
		Route::get('/', [VariablesController::class, 'index'])
			->name('variables-index')
		;
		Route::get('/{query}', [VariablesController::class, 'show'])
			->name('variable')
			->where('query', '.*')
		;
	});
});
// VARIABLE Categories
Route::prefix('user-variables')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/', [UserVariablesController::class, 'index'])
        ->name('user-variables-index')
    ;
    Route::get('/{id}', [UserVariablesController::class, 'show'])
        ->name('user-variable');
       // ->where('name', '.*')
});
// VARIABLE Categories
Route::prefix('variable-categories')->group(function () {
    Route::get('/', [VariableCategoriesController::class, 'index'])
        ->name('variable-categories-index')
    ;
    Route::get('/{name}', [VariableCategoriesController::class, 'show'])
        ->name('variable-category')
        ->where('name', '.*')
    ;
});
// TREATMENTS
Route::prefix('treatments')->group(function () {
    Route::get('/', function () {
        return (new TreatmentsVariableCategory)->getShowPageView();
    });
    Route::get('/{query}', [VariablesController::class, 'show'])
        ->name('treatments-search')
        ->where('query', '.*')
    ;
});
Route::prefix('symptoms')->group(function () {
    Route::get('/', function () {
        return (new SymptomsVariableCategory())->getShowPageView();
    });
    Route::get('/{query}', [VariablesController::class, 'show'])
        ->name('symptoms-search')
        ->where('query', '.*')
    ;
});
Route::prefix('population-studies')->group(function () {
    Route::get('/', [PopulationStudiesController::class, 'index'])
        ->name('population-studies-index')
    ;
});
// Individual Studies
Route::prefix('individual-studies')->group(function () {
    Route::get('/', [IndividualStudiesController::class, 'index'])
        ->name('individual-studies-index')
    ;
});
// Cohort Studies
Route::prefix('cohort-studies')->group(function () {
    Route::get('/', [CohortStudiesController::class, 'index'])
        ->name('cohort-studies-index')
    ;
});
if(config('queue.default') != 'redis'){
	Route::prefix('horizon')->group(function () {
		Route::queueMonitor();
	});
}
Route::get('phpunit', [Admin\PHPUnitController::class, 'runPHPUnitTest'])->name('admin.phpunit');
Route::post('phpunit', [Admin\PHPUnitController::class, 'runPHPUnitTest'])->name('admin.phpunit.post');
// ADMIN
Route::prefix('admin')->middleware(AdminMiddleware::NAME)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin-dashboard');
    //Route::get('analyze', function (\Illuminate\Http\Request $request) {return view('admin-dashboard');})->name('admin-dashboard');
    //Route::get('admin', function (\Illuminate\Http\Request $request) {return view('admin-dashboard');})->name('admin-dashboard');
    Route::resource('user-management', UserManagementController::class)->except('show');
	Route::get('ignitionReport', [Admin\IgnitionController::class, 'show'])->name('admin.ignitionReport');
    Route::get('invalid-measurements', function () {
        $v = QMVariable::fromRequest()->getCommonVariable();
        $m = $v->getInvalidMeasurements();
        return view('ifttt.list', ['buttons' => $m]);
    });
	Route::get('env', [Admin\EnvController::class, 'get'])->name('admin.env');
    Route::get('issues', [Admin\IssuesController::class, 'index'])->name('admin.issues');
    Route::get('slow-queries', function (\Illuminate\Http\Request $request) {
        return view('pages.slow-queries');
    })->name('slow-queries');
    Route::get('cleanup', function () {
        $v = QMVariable::fromRequest();
        $v->cleanup();
    })->name('cleanup');
    Route::get(UrlHelper::PATH_CLEANUP_SELECT, function () {
        $select = QMRequest::getParam('select');
        $result = Writable::selectStatic($select);
	    if($result){
	        $result = HtmlHelper::arrayToTable($result);
	    } else {
	        $result = "<h3>No Records Found Matching Query</h3>";
	    }
	    $update = QMRequest::getParam("update");
        $params = QMRequest::getQuery();
        $params['selectQuery'] = SqlFormatter::format($select);
        $params['results'] = $result;
        if($update){
            $params['updateUrl'] = UrlHelper::getCleanupUpdateUrl($select, $update, $params['message']);
            $params['updateQuery'] = SqlFormatter::format($update);
        }
        return view('pages.'.UrlHelper::PATH_CLEANUP_SELECT, $params);
    })->name(UrlHelper::PATH_CLEANUP_SELECT);
    Route::get(UrlHelper::PATH_CLEANUP_UPDATE, function () {
        $sql = QMRequest::getParam('update');
        $params = QMRequest::getQuery();
        $params['updateQuery'] = SqlFormatter::format($sql);
        $params['result'] = Writable::statementStatic($sql)[0]['result'];
        return view('pages.'.UrlHelper::PATH_CLEANUP_UPDATE, $params);
    })->name(UrlHelper::PATH_CLEANUP_UPDATE);
    Route::get('create/migration', [CreateFileController::class, 'createMigration'])->name('admin.create.migration');
    Route::get('create/solution', [CreateFileController::class, 'createSolution'])->name('admin.create.solution');
    Route::get('create/exception', [CreateFileController::class, 'createException'])->name('admin.create.exception');
    Route::get('create/controller', [CreateFileController::class, 'createController'])->name('admin.create.controller');
    Route::get('solution', [Admin\SolutionController::class, 'runSolution'])->name('admin.solution');
    Route::get('logs/nginx/error', [LogController::class, 'getNginxErrorLog'])->name('admin.logs.nginx.error');
    Route::get('logs/nginx/access', [LogController::class, 'getNginxAccessLog'])->name('admin.logs.nginx.access');
    Route::get('logs/php', [LogController::class, 'getPHPLog'])->name('admin.logs.php');
    Route::get('exception', function (\Illuminate\Http\Request $request) {
        throw new LogicException("This is a demo exception");
    })->name('admin.exception');
    Route::get(FixInvalidRecordsController::PATH, [Admin\FixInvalidRecordsController::class, 'fixInvalidRecords']);
});
// DATALAB
Route::prefix('datalab')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/', [BaseDataLabController::class, 'dashboard'], ["as" => 'datalab']);
    Route::resource('aggregateCorrelations', DataLab\GlobalVariableRelationshipController::class, ["as" => 'datalab']);
    Route::resource('applications', DataLab\ApplicationController::class, ["as" => 'datalab']);
    Route::resource('collaborators', DataLab\CollaboratorController::class, ["as" => 'datalab']);
    Route::resource('commonTags', DataLab\CommonTagController::class, ["as" => 'datalab']);
    Route::resource('connections', DataLab\ConnectionController::class, ["as" => 'datalab']);
    Route::resource('connectorImports', DataLab\ConnectorImportController::class, ["as" => 'datalab']);
    Route::resource('connectorRequests', DataLab\ConnectorRequestController::class, ["as" => 'datalab']);
    Route::resource('connectors', DataLab\ConnectorController::class, ["as" => 'datalab']);
    Route::resource('user_variable_relationships', DataLab\CorrelationController::class, ["as" => 'datalab']);
    Route::resource('deviceTokens', DataLab\DeviceTokenController::class, ["as" => 'datalab']);
    Route::resource('measurementExports', DataLab\MeasurementExportController::class, ["as" => 'datalab']);
    Route::resource('measurementImports', DataLab\MeasurementImportController::class, ["as" => 'datalab']);
    Route::resource('measurements', DataLab\MeasurementController::class, ["as" => 'datalab']);
    Route::resource('notifications', DataLab\NotificationController::class, ["as" => 'datalab']);
    Route::resource('oAuthAccessTokens', DataLab\OAAccessTokenController::class, ["as" => 'datalab']);
    Route::resource('oAuthClients', DataLab\OAClientController::class, ["as" => 'datalab']);
    Route::resource('posts', DataLab\WpPostController::class, ["as" => 'datalab']);
    Route::resource('purchases', DataLab\PurchaseController::class, ["as" => 'datalab']);
    Route::resource('sentEmails', DataLab\SentEmailController::class, ["as" => 'datalab']);
    Route::resource('studies', DataLab\StudyDataLabController::class, ["as" => 'datalab']);
    Route::resource('subscriptions', DataLab\SubscriptionController::class, ["as" => 'datalab']);
    Route::resource('trackingReminderNotifications', DataLab\TrackingReminderNotificationController::class, ["as" => 'datalab']);
    Route::resource('trackingReminders', DataLab\TrackingReminderController::class, ["as" => 'datalab']);
    Route::resource('units', DataLab\UnitController::class, ["as" => 'datalab']);
    Route::resource('unitCategories', DataLab\UnitCategoryController::class, ["as" => 'datalab']);
    Route::resource('users', DataLab\UserController::class, ["as" => 'datalab']);
    Route::resource('userTags', DataLab\UserTagController::class, ["as" => 'datalab']);
    Route::resource('userVariables', DataLab\UserVariableController::class, ["as" => 'datalab']);
    Route::resource('variableCategories', DataLab\VariableCategoryController::class, ["as" => 'datalab']);
    Route::resource('variables', DataLab\VariableController::class, ["as" => 'datalab']);
    Route::resource('votes', DataLab\VoteController::class, ["as" => 'datalab']);

    Route::get('/variable-search', function () {
        return view('variable-search-autocomplete');
    });
    Route::get('/root-cause', RootCauseAnalysisController::class);
});
Route::get('/root-cause', RootCauseAnalysisController::class);
// OPEN
Route::prefix('open')->group(function () {
    Route::get('/echo', function () {
        $html = QMRequest::getParam('html');
        return new Response($html, 200, [
            'Content-Type' => MimeContentTypeHelper::HTML,
        ]);
    });
    Route::get('/search', function () {
        return view('variable-search-autocomplete');
    });
});
// AUTH
Route::middleware(QMAuthenticate::NAME)->group(function () {
	Route::prefix('chat')->group(function () {
		Route::post('/', ChatGPT::class . '@post');
		Route::delete('/', ChatGPT::class . '@delete');
		Route::post('send-message', ChatGPT::class . '@sendMessage');
		Route::get('event-stream', ChatGPT::class . '@eventStream');
	});
    Route::get('notification-test', function(){
        return view('notification-test');
    });
    Route::get('global-variable-relationships-list', function (){
        $aggregateCorrelations = GlobalVariableRelationship::query()->limit(10)->get();
        $headers = ['Cause', 'Effect', 'Score'];
        $rows = [];
        foreach($aggregateCorrelations as $c){
            $rows[]= [$c->getCauseVariable()->name, $c->getEffectVariable()->name, $c->aggregate_qm_score];
        }
        return view('pages.correlations_list', ['headers' => $headers, 'rows' => $rows]);
    })->name('global-variable-relationships-list');
    Route::get('/food/{query}', function ($query) {
        $body = FoodCentralWrapper::search($query);
        return JsonResponse::create($body);
    })->where('query', '.*');
});
// USER
Route::prefix('user')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('user-dashboard');
    Route::get('profile', [ProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('user.profile.password');
});
// PHYSICIAN
Route::prefix('physician')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/', [AppsController::class, 'getIndex'])->name('physicians');
    Route::post('share', [AppsController::class, 'postShareUserData']);
    Route::get('create', [AppsController::class, 'getCreate'])->name('create/physician');
    Route::post('create', [AppsController::class, 'postCreate']);
    Route::get('{clientOrAppId}/edit', [AppsController::class, 'getEdit'])->name('update/physician');
    Route::post('{clientOrAppId}/edit', [AppsController::class, 'postEdit']);
    Route::get('{clientOrAppId}/delete', [AppsController::class, 'getDelete'])->name('delete/physician');
    Route::get('{clientOrAppId}/confirm-delete', [AppsController::class, 'getModalDelete'])->name('confirm-delete/physician');
    Route::post('{clientOrAppId}/add-collaborator', [AppsController::class, 'postAddCollaborator'])->name('collaborator/physician');
    Route::post('delete-collaborator', [AppsController::class, 'postDeleteCollaborator'])->name('collaborator-delete/physician');
});
Route::get('connect', function () {
	return SocialAuthController::getConnectorsListHtml();
})->middleware(QMAuthenticate::NAME);
Route::get('data-sources', function () {
	return SocialAuthController::getConnectorsListHtml();
})->middleware(QMAuthenticate::NAME);
// DEV - Don't require auth for dev routes because phpstorm links and reloads are slowed
Route::prefix('dev')->group(function () {
    if(!EnvOverride::isLocal()){return;}
	Route::get('aapanel/{name}', AAPanelController::class)->name('aapanel');
    Route::get('phpstorm', [Admin\PHPStormController::class, 'get'])->name('dev.phpstorm');
    Route::get('test', function () {
        return GetTestController::saveTestAndRedirectToPHPStorm();
    });
    Route::get('reports/email/{shortClassName}', function ($shortClassName) {
        $r = AnalyticalReport::getDemoByClassName($shortClassName);
        return new Response($r->generateEmailBody(), 200, [
            'Content-Type' => MimeContentTypeHelper::HTML,
        ]);
    });
    Route::get('view/{viewPath}', function ($viewPath) {
        return view($viewPath);
    })->where('viewPath', '.*'); // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
    Route::get('pdf', function () {
        $path = BarChartButton::generateTestPDF();
        return response()->file($path);
    });
    Route::get('dompdf', function () {
        $html = BarChartButton::getTestHtml();
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->stream();
    });
    Route::get('html', function () {
        $html = BarChartButton::getTestHtml();
        return new Response($html, 200, [
            'Content-Type' => MimeContentTypeHelper::HTML,
        ]);
    });
    Route::get('rate-limit', function () {
        return "<h1>" . QMAPIRepo::checkRateLimits() . "</h1>";
    });
	Route::get('study', [StudiesController::class, 'demo'])
	     ->name('study-dev');
	Route::get('email-test', [EmailController::class, 'sendTest'])
	     ->name('email-test');
});
Route::get('study', [StudiesController::class, 'show'])
     ->name('study');
// EXAMPLES
Route::prefix('examples')->middleware(QMAuthenticate::NAME)->group(function () {
    Route::get('/study', [StudiesController::class, 'show'])
        ->name('example-study');
    Route::get('mailable', function () {
        $user = User::findInMemoryOrDB(230);
        return new PostListMail($user->user_email);
    });
    Route::get('/email', function () {
        $u = User::getById(230);
        $mail = $u->getWpPostPreviewTableMail("Preview text here", 10);
        return $mail;
    });
    Route::get('dashboard', [Admin\ExampleDashboardController::class, 'index'])
        ->name('example-dashboard');
    Route::get('table-list', function () {
        return view('pages.table_list');
    })->name('table');
    Route::get('typography', function () {
        return view('pages.typography');
    })->name('typography');
    Route::get('icons', function () {
        return view('pages.icons');
    })->name('icons');
    Route::get('map', function () {
        return view('pages.map');
    })->name('map');
    Route::get('notifications', function () {
        return view('pages.notifications');
    })->name('notifications');
    Route::get('rtl-support', function () {
        return view('pages.language');
    })->name('language');
    Route::get('upgrade', function () {
        return view('pages.upgrade');
    })->name('upgrade');
});
Route::prefix('auth')->group(function () {
	Route::get('nonce/{type?}', [AuthController::class, 'nonce']);
	Route::post('web3/login', [AuthController::class, 'web3Login']);
	Route::post('web3/register', [AuthController::class, 'web3Register']);
	Route::post('web3/connect', [AuthController::class, 'web3Connect'])
	     ->middleware(QMAuthenticate::NAME);
	Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
	     ->name('password.request');
	Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
	     ->name('password.email');
	Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
	     ->name('password.reset');
	Route::post('password/reset', [ResetPasswordController::class, 'reset'])
	     ->name('password.update');
    // Authentication routes...
    Route::get('login', [LoginController::class, 'getLogin'])->name('auth.login');
    Route::post('login', [LoginController::class, 'authenticate']);
    Route::get('logout', [LoginController::class, 'getLogout']);
	Route::post('logout', [LoginController::class, 'logout']);
    // Registration routes...
    Route::get('register', [RegisterController::class, 'getRegister'])->name('auth.register');
    Route::post('register', [RegisterController::class, 'postRegister']);
    Route::get( 'password', [Web\AccountController::class, 'password'])->name('account.password');
    Route::post( 'password', [Web\AccountController::class, 'postPassword'])->name('account.password.post');
	Route::get('{provider}', [SocialAuthController::class, 'webAuthCallback'])->name('auth.social.callback');
    Route::prefix('social')->middleware('guest')->group(function () {
        Route::get('login', [SocialAuthController::class, 'login'])->name('auth.social.login');
        Route::get('webLogin', [SocialAuthController::class, 'webLogin'])->name('auth.social.webLogin');
        Route::get('authorizeCode', [SocialAuthController::class, 'authorizeCode'])->name('auth.social.authorize');
        Route::get('authorizeToken', [SocialAuthController::class, 'authorizeToken'])->name('auth.social.token');
        Route::get('webCallback/{provider}', [SocialAuthController::class, 'webAuthCallback'])->name('auth.social.callback');
    });
});

// API V2
Route::prefix('api/v2')->group(function () {
		Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
		     ->name('password.request.v2');
		Route::post('password/email.v2', [ForgotPasswordController::class, 'sendResetLinkEmail'])
		     ->name('password.email.v2');
		Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
		     ->name('password.reset.v2');
		Route::post('password/reset', [ResetPasswordController::class, 'reset'])
		     ->name('password.update.v2');
        Route::prefix('file')->middleware(QMAuthenticate::NAME)->group(function () {
            // upload
            Route::post("/", [FileUploadController::class, 'uploadUserFile']);
//            Route::post('/', [
//                'uses' => [\UniSharp\LaravelFilemanager\Controllers\UploadController::class, 'upload'],
//                'as' => 'unisharp.lfm.upload',
//            ]);
            // download
            Route::get("/", [FileUploadController::class, 'downloadUserFile']);
//            Route::get('/', [
//                'uses' => [\UniSharp\LaravelFilemanager\Controllers\DownloadController::class, 'getDownload'],
//                'as' => 'getDownload',
//            ]);
            // delete
//            Route::delete('/', [
//                'uses' => [\UniSharp\LaravelFilemanager\Controllers\DeleteController::class, 'getDelete'],
//                'as' => 'getDelete',
//            ]);
        });
        Route::get( '/email/fitbit', function () { return view('email.fitbit', ['userName' => 'Mike', 'trackingMessage' => 'Time to track!', 'unsubscribeLink' => "" ]); } );
        Route::get( '/security', function () { return view('web.security-policy', []); } );
        Route::get( '/privacy', function () { return view('web.privacy-policy', []); } );
        Route::get( '/image-credits', function () { return view('web.image-credits', []); } );
        Route::get( '/platform-terms-of-service', function () { return view('web.platform-terms-of-service', []); } );
        Route::get( '/end-user-terms-of-service', function () { return view('web.end-user-terms-of-service', []); } );
        Route::get( '/tos', function () { return view('web.end-user-terms-of-service', []); } );
        Route::get( '/tracking-reminder-notifications', function () { return view('email.tracking-reminder-notifications', ['userName' => 'Mike', 'trackingMessage' => 'Time to track!']); } );
        Route::get( '/tracking-reminder-notifications-faces', function () {
            return view('email.tracking-reminder-notifications-faces', ['userName' => 'Mike', 'trackingMessage' => 'Time to track!', 'unsubscribeLink' =>'']); } );
        Route::get( 'study', [StudiesController::class, 'show'])->name('v2.study');
        Route::get( '/predictors', function () { return view('email.predictors-simplified', []); } );
        Route::get( '/study-instructions-email', function () { return view('email.study-instructions', []); } );
        Route::get( '/coupon-instructions', function () { return view('email.coupon-instructions', [ 'unsubscribeLink' => "" ]); } );
        Route::get(
            '/home',
            static function () {
                $redirectUrl = IonicHelper::getIntroUrl($_GET);
                $host = QMRequest::host();
                $subDomain = Subdomain::getSubDomain($host);
                if($subDomain === 'developer'){$redirectUrl = UrlHelper::getBuilderUrl();}
                if($subDomain === 'dr'){$redirectUrl = PhysicianInvitationEmail::PHYSICIAN_URL;}
                if($subDomain === 'docs'){$redirectUrl = '/docs';}
                if($subDomain === 'studies'){$redirectUrl = QMStudy::STUDIES_URL;}
                if($subDomain === 'builder'){$redirectUrl = UrlHelper::getBuilderUrl();}
                if($subDomain === 'import'){$redirectUrl = ImportStateButton::url($_GET);}
                if(isset($_GET)){$redirectUrl = UrlHelper::addParams($redirectUrl, $_GET);}
                return redirect()->to($redirectUrl);
            }
        );
        //Read endpoints
        Route::middleware(QMAuthenticate::NAME)->group(function () {
                Route::get("user_variable_relationships", [V2\V2CorrelationController::class, 'index']);
                Route::get("user_variable_relationships/{id}", [V2\V2CorrelationController::class, 'show']);
                Route::get("measurements", [V2\V2MeasurementController::class, 'index']);
                Route::get("measurements/{id}", [V2\V2MeasurementController::class, 'show']);
                Route::get("userVariables", [V2\V2UserVariableController::class, 'index']);
                Route::get("userVariables/{id}", [V2\V2UserVariableController::class, 'show']);
                Route::get("aggregatedCorrelations", [V2\V2AggregatedCorrelationController::class, 'index']);
                Route::get("aggregatedCorrelations/{id}", [V2\V2AggregatedCorrelationController::class, 'show']);
                Route::get("units", [V2\V2UnitController::class, 'index']);
                Route::get("units/{id}", [V2\V2UnitController::class, 'show']);
                Route::get("variableCategories", [V2\V2VariableCategoryController::class, 'index']);
                Route::get("variableCategories/{id}", [V2\V2VariableCategoryController::class, 'show']);
                Route::get("variables", [V2\V2VariableController::class, 'index']);
                Route::get("variables/{id}", [V2\V2VariableController::class, 'show']);
                Route::prefix('public')->group(function () { Route::get('variables', [PublicVariableController::class, 'index']); } );
            }
        );

        Route::middleware(QMAuthenticate::NAME)->group(function () {
                Route::post("user_variable_relationships", [V2\V2CorrelationController::class, 'store']);
                Route::post("email", [EmailController::class, 'postEmail']);
                Route::post("measurements/request_csv", [V2\V2MeasurementController::class, 'postCsvExportRequest']);
                Route::post("measurements/request_pdf", [V2\V2MeasurementController::class, 'postPdfExportRequest']);
                Route::post("measurements/request_xls", [V2\V2MeasurementController::class, 'postXlsExportRequest']);
                Route::post("measurements", [V2\V2MeasurementController::class, 'store']);
                Route::put("measurements/{id}", [V2\V2MeasurementController::class, 'update']);
                Route::delete("measurements/{id}", [V2\V2MeasurementController::class, 'destroy']);
                Route::post("userVariables", [V2\V2UserVariableController::class, 'store']);
                Route::post("spreadsheetUpload", [FileUploadController::class, 'storeSpreadsheet']);
                Route::post("upload", [FileUploadController::class, 'uploadClientFile']);
                Route::get("download", [FileUploadController::class, 'downloadAndDecrypt']);
                Route::post("variables", [V2\V2VariableController::class, 'store']);
                Route::post("units", [V2\V2UnitController::class, 'store']);

	        # Application Management
	        Route::prefix('apps')->group(function () {
		        Route::get('/', [AppsController::class, 'getIndex'])->name('apps');
		        Route::get('create', [AppsController::class, 'getCreate'])->name('create/app');
		        Route::post('create', [AppsController::class, 'postCreate']);
		        Route::get('{clientOrAppId}/edit', [AppsController::class, 'getEdit'])->name('update/app');
		        Route::get('{clientOrAppId}/integration', [AppsController::class, 'getIntegration'])->name('integration/app');
		        Route::post('{clientOrAppId}/edit', [AppsController::class, 'postEdit']);
		        Route::get('{clientOrAppId}/delete', [AppsController::class, 'getDelete'])->name('delete/app');
		        Route::get('{clientOrAppId}/confirm-delete', [AppsController::class, 'getModalDelete'])->name('confirm-delete/app');
		        Route::post('{clientOrAppId}/add-collaborator', [AppsController::class, 'postAddCollaborator'])->name('collaborator/app');
		        Route::post('delete-collaborator', [AppsController::class, 'postDeleteCollaborator'])->name('collaborator-delete/app');
	        });
            }
        );
        Route::get( 'study/{clientId}', [StudiesController::class, 'getLandingPage'])->name('study.landing');
        Route::post( 'login', function () {
            return LoginController::apiLogin();
        });
        Route::apiResource( '/analyze', UserStudyAPIController::class);
        Route::post( '/user', [UsersController::class, 'store'])->name('users.post');
        Route::get( '/users', [UsersController::class, 'index'])->name('users.get');
        Route::get( '/user', [UsersController::class, 'show'])->name('user.get');
        Route::post( 'register', function () {
            return RegisterController::apiRegister();
        });
        Route::get('api-docs', function () {
            return redirect(UrlHelper::DOCS_URL);
        });

		Route::group(array('prefix' => 'auth'), function () {
			// Authentication routes...
			Route::get('login', [LoginController::class, 'getLogin'])->name('auth.login');
			Route::post('login', [LoginController::class, 'authenticate']);
			Route::get('logout', [LoginController::class, 'getLogout']);
			// Registration routes...
			Route::get('register', [RegisterController::class, 'getRegister'])->name('auth.register');
			Route::post('register', [RegisterController::class, 'postRegister']);
			Route::get( 'password', [Web\AccountController::class, 'password'])->name('account.password');
			Route::post( 'password', [Web\AccountController::class, 'postPassword'])->name('account.password.post');
		});

    });
// DEBUG BAR
Route::prefix('_debugbar/assets')->group(function () {
    Route::get( '/stylesheets', [\Barryvdh\Debugbar\Controllers\AssetController::class, 'css'] );
    Route::get( '/javascript', [\Barryvdh\Debugbar\Controllers\AssetController::class, 'js'] );
});
Route::get('/_debugbar/open', [\Barryvdh\Debugbar\Controllers\OpenHandlerController::class, 'handle'])->name('debugbar-open');

Auth::routes();
Route::get('/static', function () {
    $bucket = QMRequest::getParam('bucket');
    $path = $localOrS3BucketPath = QMRequest::getParam('path');
    if($bucket){
        $localOrS3BucketPath = $bucket.'/'.$localOrS3BucketPath;
    }
    try {
        $data = GetStaticDataController::getData($localOrS3BucketPath);
    } catch (UnauthorizedException $e) {
        return LoginButton::redirectToLoginOrRegister();
    }
	if(AppMode::isLocalAPIRequest() && str_starts_with($path, 'tests/')){
		FileHelper::write($path, $data);
	}
    return new Response($data, 200, [
        'Content-Type' => MimeContentTypeHelper::guessMimeContentTypeBasedOnFileName($localOrS3BucketPath, MimeContentTypeHelper::HTML),
    ]);
})->where('localOrS3BucketPath', '.*');  // '.*' allows forward slashes https://laravel.com/docs/5.8/routing#redirect-routes
Route::get('/errors/{errorCode}', function ($errorCode) {
    return view("errors.$errorCode");
});
Route::get('/logout', [LoginController::class, 'getLogout']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get( '/privacy', function () { return view('web.privacy-policy', []); } );
Route::get('/'.ChartsController::CHARTS_PATH, [ChartsController::class, 'get'])
	->name(ChartsController::CHARTS_PATH);
Route::get('email', [EmailController::class, 'emailPreview'])->name('email-preview');
Route::get('/financial-report', function () {
    return view('financial-report');
});
Route::get('button-action', [ButtonActionController::class, 'run']);
Route::middleware(QMAuthenticate::NAME)->prefix('account')->group(function () {
		// Account management routes
		Route::get( '/', [Web\AccountController::class, 'edit'])->name('account');
		Route::get( 'subscription', [Web\AccountController::class, 'index'])->name('account.subscription');
		Route::get( 'edit', [Web\AccountController::class, 'edit'])->name('account.edit');
		Route::post( 'edit', [Web\AccountController::class, 'postEdit'])->name('account.edit.post');
		Route::post( 'upload-spreadsheet', [Web\AccountController::class, 'postSpreadsheet'])->name('account.upload.spreadsheet');
		Route::get( 'update-card', [Web\AccountController::class, 'updateCard'])->name('account.update.card');
		Route::post( 'update-card', [Web\AccountController::class, 'postUpdateCard'])->name('account.update.card.post');
		Route::post( 'subscribe', [Web\AccountController::class, 'postSubscribe'])->name('account.subscribe-post');
		Route::get( 'upgrade', [Web\AccountController::class, 'upgrade'])->name('account.upgrade');
		Route::post( 'upgrade', [Web\AccountController::class, 'postUpgrade'])->name('account.upgrade.post');
		Route::post( 'unsubscribe', [Web\AccountController::class, 'postUnsubscribe'])->name('account.unsubscribe-post');
		Route::get( 'downgrade', [Web\AccountController::class, 'downgrade'])->name('account.downgrade');
		Route::post( 'downgrade', [Web\AccountController::class, 'postDowngrade'])->name('account.downgrade.post');
		Route::get( 'autocomplete/user-variable', [Web\AccountController::class, 'userVariableAutocomplete'])->name('account.edit.autocomplete');
		Route::get( 'autocomplete/public-variable', [Web\AccountController::class, 'publicVariableAutocomplete'])->name('public.variable.autocomplete');

		Route::get( 'export-data', [Web\AccountController::class, 'exportData'])->name('account.export.data');
		Route::get( 'request-export-data/{output}', [Web\AccountController::class, 'requestExportData'])->name('account.export.request');
		Route::get( 'applications', [Web\AccountController::class, 'authorizedApps'])->name('account.authorized.apps');
		Route::get( 'delete', [Web\AccountController::class, 'deleteAccount'])->name('account.delete');
		Route::post( 'revoke-access', [Web\AccountController::class, 'revokeAccess'])->name('account.revoke.access');
		# embedded routes
		Route::get( 'common-relationships', [Web\EmbedController::class, 'commonRelationships'])->name('account.commonRelationships');
		Route::get( 'user-relationships', [Web\EmbedController::class, 'userRelationships'])->name('account.userRelationships');
		Route::get( 'reminders', [Web\EmbedController::class, 'reminders'])->name('account.reminders');
		Route::get( 'reminders-manage', [Web\EmbedController::class, 'manageReminders'])->name('account.manage.reminders');
		Route::get( 'api-explorer', [Web\EmbedController::class, 'apiExplorer'])->name('account.apiExplorer');
		Route::get( 'variables', [Web\EmbedController::class, 'variables'])->name('account.variables');
		Route::get( 'connectors', [Web\EmbedController::class, 'connectors'])->name('account.connectors');
		Route::get( 'track', [Web\EmbedController::class, 'track'])->name('account.track');
		Route::get( 'track/category/{category}', [Web\EmbedController::class, 'trackCategory'])->name('account.track.category');
		Route::get( 'history', [Web\EmbedController::class, 'history'])->name('account.history');
		Route::get( 'history/moods', [Web\EmbedController::class, 'historyMoods'])->name('account.history.moods');


	}
);


// This doesn't work for post requests and it's slow
//Route::fallback([SlimController::class, 'any']);

// These endpoints require a valid did token and fetches user's data using did token
