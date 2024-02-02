<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Files\FileHelper;
use App\Models\User;
use App\Properties\User\UserIdProperty;
use LogicException;
use App\Logging\QMLog;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs
 */
class FileCleanUpJob extends JobTestCase {
    public function testReplaceInFileNames(){
        FileHelper::replaceInFileNames("astral", 'astral');
        FileHelper::replaceInFileNames("astral", 'Astral');
    }
    public function testCreatePostsForUsers(){
        $qb = User::whereNeverPosted();
        $qb->whereNotIn(User::TABLE.'.'.User::FIELD_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
        $before = $qb->count();
        $reason = "Has no posts";
        \App\Logging\ConsoleLog::info("$before ".User::TABLE." where $reason");
        //$qb->logPreparedQuery();
        while($before && !JobTestCase::jobDurationExceedsTimeLimit()){
            $message = "$before ".User::TABLE." where $reason";
            \App\Logging\ConsoleLog::info($message);
            /** @var User $user */
            $user = $qb->first();
            $files = $user->listFilesOnS3();
            if(!$files){
                $user->firstOrCreateWpPost();
            }
            /** @var static $model */
            $model = $qb->first();
            $after = $qb->count();
            if($after >= $before){
                le("$before before and $after after where $reason");
            }
            $before = $after;
        }
    }
}
