<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use App\Models\TdddRun;
use App\Models\User;
use App\Policies\BasePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
class AuthServiceProvider extends ServiceProvider {
    /**
     * The policy mappings for the application.
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
	    'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
        // No need to add policies here because we use auto-discovery https://laravel.com/docs/5.8/authorization
    ];
    /**
     * Register any authentication / authorization services.
     * @return void
     */
    public function boot(){
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            return 'App\\Policies\\' . class_basename ( $modelClass).'Policy';
        });
        $this->registerPolicies();
	    if (! $this->app->routesAreCached()) {
		    Passport::routes();
	    }
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
	    Passport::loadKeysFrom(abs_path('storage'));
        // Don't use Auth::viaRequest('custom-token'
        // because Auth won't have all the normal functions like logout.
        // Instead, make sure that any routes requiring Auth::user() to work are within the
        // Route::group(['middleware' => 'auth'] block in web.php
        //Auth::viaRequest('custom-token', function ($request) {return ($u = QMAuth::getUser()) ? $u->l(): null;});
    }
}
