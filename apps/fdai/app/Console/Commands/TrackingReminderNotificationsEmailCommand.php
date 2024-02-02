<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

//USAGE: `php artisan trackingReminderNotifications:email`
//DEBUG: `./cli_debug.sh laravel/artisan trackingReminderNotifications:email`
namespace App\Console\Commands;
use App\Mail\TrackingReminderNotificationEmail;
use App\Models\SentEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use App\Mail\QMSendgrid;
class TrackingReminderNotificationsEmailCommand extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'trackingReminderNotifications:email {loop=ETERNAL}';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Remind to track.';
    /**
     * @throws \App\Mail\TooManyEmailsException
     */
    public function handle(){
        $argument = $this->argument('loop');
        if($argument === 'ETERNAL'){
            $randomSeconds = mt_rand(0, 3600);
            Log::info('ETERNAL so sleeping '.$randomSeconds.' seconds before starting to avoid racing conditions...');
            sleep($randomSeconds);
        }
        while(true){
            $hoursSinceLastEmail = 0;
            $latestEmail = SentEmail::whereType(QMSendgrid::SENT_EMAIL_TYPE_TRACKING_REMINDER_NOTIFICATIONS)
                ->latest()
                ->first();
            if($latestEmail){
                $hoursSinceLastEmail = Carbon::now()->diffInHours($latestEmail->created_at);
            }
            if($hoursSinceLastEmail < 1 && $argument !== 'DEBUG'){
                Log::info('Sleeping an hour before checking for more TrackingReminderNotifications to email  ');
            }else{
                $userRows = TrackingReminderNotificationEmail::usersQB();
                foreach ($userRows as $userRow){
                    $response[] = TrackingReminderNotificationEmail::sendIt($userRow->email);
                }
                Log::debug('sendTrackingReminderNotificationsEmail finished.  Sleeping an hour before checking again.');
            }
            if($argument == 'ONCE'){
                break;
            }
            sleep(3600);
        }
    }
}
