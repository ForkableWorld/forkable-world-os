<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\DataSources\QMClient;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\OAAccessToken;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Auth\QMAccessToken;
/** @package App\PhpUnitJobs
 */
class ReplaceTextJob extends JobTestCase {
    public function testReplaceTextInAllFilesRecursively(){
        FileHelper::replaceTextInAllFilesRecursively("C:\code", "__SEARCH__", "__REPLACE__",'.env');
    }
    private static function deleteGhostInspectorTokens(){
        $qb = QMAccessToken::readonly()->whereRaw(OAAccessToken::FIELD_CLIENT_ID, '%Ghost%');
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " " . __FUNCTION__ . " to delete");
            $qb->delete();
        }
    }
    private static function deleteAccessTokensWithPeriodsInClientId(){
        $qb = QMAccessToken::readonly()->whereRaw(OAAccessToken::FIELD_CLIENT_ID, '%.%');
        //$qb->where(\App\Models\OAAccessToken::FIELD_USER_ID, 18535);
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " " . __FUNCTION__ . " to delete");
            $qb->update([
                OAAccessToken::FIELD_DELETED_AT => now_at(),
                OAAccessToken::FIELD_EXPIRES    => now_at()
            ]);
        }
    }
    private static function changeClientIdToQuantiModoWhereClientIsChromeExtension(){
        $qb = QMAccessToken::readonly()->whereLike(OAAccessToken::FIELD_CLIENT_ID, '%and apiUrl is%');
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " " . __FUNCTION__ . " to update");
            $qb->update([QMClient::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_QUANTIMODO]);
        }
    }
    private static function changeClientIdToQuantiModoWhereClientIdIsGoogle(){
        $qb = QMAccessToken::readonly()
            ->whereLike(OAAccessToken::FIELD_CLIENT_ID, '%1052648855194.apps.googleusercontent.com%');
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " " . __FUNCTION__ . " to update");
            $qb->update([QMClient::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_QUANTIMODO]);
        }
    }
    private static function changeClientIdToQuantiModoWhereClientIdIsMozilla(){
        $qb = QMAccessToken::readonly()->whereLike(OAAccessToken::FIELD_CLIENT_ID, '%Mozilla%"');
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " " . __FUNCTION__ . " to update");
            $qb->update([QMClient::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_QUANTIMODO]);
        }
    }
}
