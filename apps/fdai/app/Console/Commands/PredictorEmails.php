<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

//USAGE: `php artisan email:predictors DEBUG`
//DEBUG: `./cli_debug.sh laravel/artisan email:predictors DEBUG`
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\PhpUnitJobs\Mail\PredictorEmailJob;
class PredictorEmails extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'email:predictors {loop=ETERNAL}';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Sends predictor emails to users';
    /**
     * Execute the console command.
     * @throws \Exception
     * @internal param StripeService $stripe
     */
    public function handle(){
        PredictorEmailJob::sendPredictorEmails();
    }
}
