<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Analytics;
use App\Models\User;
use App\Storage\DB\QMQB;
use App\Storage\DB\QMDB;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
class RootCauseJobTest extends JobTestCase {
    private const FREQUENCY_IN_DAYS = 30;
    public function testRootCauseJob(): void{
        $posts = User::postWhereNeverPosted();
        $this->assertGreaterThan(0, count($posts));
    }
    /**
     * @return QMUser
     */
    public static function getLeastRecentlyEmailedUser(): ?QMUser{
        $row = QMUser::readonly()
            ->where(User::FIELD_UNSUBSCRIBED, false)
            ->whereNull(User::FIELD_DELETED_AT)
            ->where(function($q) {
                /** @var QMQB $q */
                $q->where(User::FIELD_LAST_EMAIL_AT, '<', db_date(time() - self::FREQUENCY_IN_DAYS * 86400))
                    ->orWhereNull(User::FIELD_LAST_EMAIL_AT);
            })
            ->orderBy(User::FIELD_LAST_EMAIL_AT, 'ASC')
            ->first();
        if(!$row){return null;}
        return QMUser::instantiateIfNecessary($row);
    }
}
