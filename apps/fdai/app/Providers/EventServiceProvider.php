<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use App\Listeners\AuthenticatedListener;
use App\Listeners\LogArtisanFinished;
use App\Listeners\LogArtisanStarting;
use App\Listeners\LoginAttemptingListener;
use App\Listeners\LoginFailedListener;
use App\Listeners\LoginListener;
use App\Listeners\LogoutListener;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Logout::class => [
            LogoutListener::class,
        ],
	    Login::class => [
		    LoginListener::class,
	    ],
	    Attempting::class => [
		    LoginAttemptingListener::class,
	    ],
	    Authenticated::class => [
		    AuthenticatedListener::class,
	    ],
	    Failed::class => [
		    LoginFailedListener::class,
	    ],
	    CommandFinished::class => [
		    LogArtisanFinished::class . '@handle',
	    ],
	    CommandStarting::class => [
		    LogArtisanStarting::class . '@handle',
	    ],
	    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
		    // add your listeners (aka providers) here
		    'SocialiteProviders\\Twitter\\TwitterExtendSocialite@handle',
	    ],
    ];
    /**
     * Register any events for your application.
     * @return void
     */
    public function boot(){
        //
    }
}
