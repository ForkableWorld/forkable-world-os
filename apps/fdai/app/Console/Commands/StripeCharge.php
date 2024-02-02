<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Console\Commands;
use App\Exceptions\ExceptionHandler;
use App\Services\StripeService;
use Illuminate\Console\Command;
class StripeCharge extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'stripe:charge';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Charge applications with exceeded calls at the end of their subscription';
    /**
     * Execute the console command.
     * @param StripeService $stripe
     * @return mixed
     */
    public function handle(StripeService $stripe){
        $this->info("Charging for extra calls");
        try {
            $stripe->chargeExceedingCalls();
        } catch (\Exception $e) {
            $this->error('StripeService: '.$e->getMessage());
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
        }
    }
}
