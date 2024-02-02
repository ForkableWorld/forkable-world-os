<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\Models\User;
use App\Astral\Actions\DeleteTestUsersAction;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserUserLoginProperty;
use Illuminate\Support\Arr;
use App\Exceptions\InvalidClientIdException;
use App\DataSources\Connectors\QuantiModoConnector;
use App\DataSources\QMClient;
use App\Storage\DB\Writable;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
use Throwable;
class UserCleanUpJobTest extends JobTestCase {
    public static function deleteOldTestUsers(){
        DeleteTestUsersAction::deleteOldTestUsers();
    }
    public function testDeleteTimeZoneOffset(){
        /** @var User[] $users */
        $users = User::whereNull(User::FIELD_TIMEZONE)
            ->whereNotNull(User::FIELD_TIME_ZONE_OFFSET)
            ->get();
        QMLog::info(count($users)." users");
        foreach($users as $u){
            $tz = $u->getQMUser()->getTimezone();
            $u->logInfo("offset: $u->time_zone_offset | timezone: $tz");
            $u->timezone = $tz;
            $u->save();
        }
    }
    public static function testFixUsersWithEmptyDisplayNames(){
        $users = User::where(User::FIELD_DISPLAY_NAME, "")->get();
        /** @var User $user */
        foreach($users as $user){
            $user->logInfo("");
            $name = str_replace('-', " ", $user->user_login);
            $user->display_name = QMStr::titleCaseSlow($name);
            $user->save();
        }
    }
    public static function createSystemUser(): void{
        \App\Logging\ConsoleLog::info(__FUNCTION__);
        try {
            User::query()->insert([
                User::FIELD_ID    => UserIdProperty::USER_ID_SYSTEM,
                'client_id'       => BaseClientIdProperty::CLIENT_ID_SYSTEM,
                'user_login'      => BaseClientIdProperty::CLIENT_ID_SYSTEM,
                'user_email'      => BaseClientIdProperty::CLIENT_ID_SYSTEM . '@quantimo.do',
                'user_registered' => now_at()
            ]);
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
    }
    public function testFixMissingUserClientIds(){
        $qb = QMUser::writable()->whereNull(User::FIELD_CLIENT_ID);
        $result = $qb->update([User::FIELD_CLIENT_ID => QuantiModoConnector::NAME]);
        $stupid = [
            "",
            "UnknownConnectorClientId",
            "1052648855194.apps.googleusercontent.com",
            "Mozilla/5.0 (X11; Linux x86_64; rv:45.9.0) Gecko/20100101 Firefox/45.9.0 Ghost I",
            "fearofdefeat.tailordd-mfp-app",
            "Web"
        ];
        foreach($stupid as $item){
            $qb = QMUser::writable()->where(User::FIELD_CLIENT_ID, $item);
            $result3 = $qb->update([User::FIELD_CLIENT_ID => QuantiModoConnector::NAME]);
        }
        $users = QMUser::getAll();
        $noClient = Arr::where($users, static function($user){
            /** @var QMUser $user */
            if (empty($user->clientId)){return true;}
            try {
                BaseClientIdProperty::validateClientId($user->clientId);
                return false;
            } catch (InvalidClientIdException $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
                return true;
            }
        });
        $count = count($noClient);
        if($count){QMLog::error("$count users have no client id!");}
        foreach($noClient as $u){
            $u->logInfoWithoutContext($u->clientId);
        }
    }
    public function testSlugifyLoginNames(){
        self::slugifyLoginNames();
    }
    public static function slugifyLoginNames(){
        $users = QMUser::getAll();
        foreach($users as $user){
            $loginName = $user->getLoginName();
            $slugified = UserUserLoginProperty::sanitize($loginName);
            if($loginName !== $slugified){
                QMLog::info("Updating login name from $loginName to $slugified because it's not slugified!");
                try {
                    $user->updateDbRow([User::FIELD_USER_LOGIN => $slugified]);
                } catch (Throwable $exception){
                    QMLog::info($exception->getMessage());
                }
            }
        }
    }
}
