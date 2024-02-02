<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use DutchCodingCompany\FilamentSocialite\Facades\FilamentSocialite;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\DevOps\XDebug;
use App\Logging\ConsoleLog;
use App\Logging\QMBugsnag;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Socialite\Contracts\User;
use Queue;
class AppServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot(){
	    $platform = DB::getDoctrineSchemaManager()->getDatabasePlatform();
	    $platform->registerDoctrineTypeMapping('enum', 'string');

	    $platform = DB::getDoctrineConnection()->getDatabasePlatform();
	    $platform->registerDoctrineTypeMapping('enum', 'string');

	    FilamentSocialite::setCreateUserCallback(function (\Laravel\Socialite\Contracts\User $oauthUser,
                                                     FilamentSocialite $socialite) {
            /** @var \App\Models\User $userModelClass */
            $userModelClass = $socialite->getUserModelClass();
	        $config = $socialite->getConfig();
	        \App\Models\User::createNewUser([
                \App\Models\User::FIELD_USER_LOGIN => $oauthUser->getName(),
                \App\Models\User::FIELD_USER_EMAIL => $oauthUser->getEmail(),
            ],                              $config['Label']);
        });
        //WpPost::observe(PostObserver::class);
        Queue::before(function (JobProcessing $event) {
            AppMode::setIsWorker(true);
            //User::mike()->notify(new JobStartedNotification($event));
        });
        Queue::after(function (JobProcessed $event) {
            //User::mike()->notify(new JobCompletedNotification($event));
        });
        Queue::failing(function (JobFailed $event) {
            //User::mike()->notify(new JobFailedNotification($event));
        });

        // Blade custom directives for isAdmin
        Blade::directive('isadmin', function() {
            return "<?php if(Auth::user() && Auth::user()->isAdmin()): ?>";
        });
        Blade::directive('endisadmin', function() {
            return "<?php endif ?>";
        });

        Bugsnag::registerCallback(function ($report) {
            QMBugsnag::preSendReportConfigCallback($report);
        });

        // https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel
        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        Str::contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                $query->where($relationAttribute, \App\Storage\DB\ReadonlyDB::like(), "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, \App\Storage\DB\ReadonlyDB::like(), "%{$searchTerm}%");
                        }
                    );
                }
            });

            return $this;
        });
    }
    /**
     * Register any application services.
     * @return void
     */
    public function register(){
        Cashier::ignoreMigrations();
        if(\App\Utils\Env::get('TELESCOPE_ENABLED')){
            //$this->app->register(TelescopeServiceProvider::class);
        }
        $env = $this->app->environment();
        $rootLocal = EnvOverride::isLocal();
        $loadDevProviders = $env === Env::ENV_LOCAL || XDebug::active() || $rootLocal;
        if($loadDevProviders && !class_exists('Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')){
            $loadDevProviders = false;
            ConsoleLog::info("Not loading dev service providers because IdeHelperServiceProvider not found. ".
                "You probably ran composer install --no-dev.");
        }
        if($loadDevProviders){
            $this->app->register(IdeHelperServiceProvider::class);
            //$this->app->register(TerminalServiceProvider::class);
            //$this->app->register(CodersServiceProvider::class);
            //$this->app->register(\PragmaRX\Health\ServiceProvider::class);
            //$this->app->register(DuskServiceProvider::class);
            //$this->app->register(\Barryvdh\Debugbar\ServiceProvider::class); // Lets install this on production because it's only enabled if APP_DEBUG=true
            //$this->commands(DuskCommand::class);
            //$this->app->register(\Jijoel\ValidationRuleGenerator\ServiceProvider::class);
        }
        // UserVariable::observe(UserVariableObserver::class); breaks \Watson\Validating\ValidatingTrait::bootValidatingTrait for some reason
    }
}
