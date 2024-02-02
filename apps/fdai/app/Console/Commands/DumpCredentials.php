<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Console\Commands;
use App\Models\Credential;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Log;
use App\Utils\AppMode;
class DumpCredentials extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'dump:credentials';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Dumps credentials table to use in unit tests';
    /**
     * Execute the console command.
     * @return mixed
     * @internal param StripeService $stripe
     */
    public function handle(){
        $credentials = Credential::all([
                'user_id',
                'connector_id',
                'attr_key',
                'attr_value'
            ]);
        $headers = [
            'User',
            'Connector',
            'Key',
            'Value'
        ];
        $file = fopen('credentials.csv', 'w');
        fwrite($file, implode(',', $headers)."\n");
        foreach($credentials as $credential){
            if(isset($credential->attr_value) && isset($credential->attr_key) && isset($credential->connector->name)){
                $dump = [
                    'user_id'    => $credential->user_id,
                    'connector'  => $credential->connector->name,
                    'attr_key'   => $credential->attr_key,
                    'attr_value' => base64_encode($credential->attr_value)
                ];
                fwrite($file, implode(',', $dump)."\n");
            }
        }
        fclose($file);
        $this->info('Dumped credentials, check laravel/credentials.csv');
    }
}
