<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use App\Http\Middleware\QMAuthenticate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
class BroadcastServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot(){
        Broadcast::routes(['middleware' => ['web', QMAuthenticate::NAME]]);
        require base_path('routes/channels.php');
    }
}
