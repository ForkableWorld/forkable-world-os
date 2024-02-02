<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\Files\Spreadsheet\CsvFile;
use App\Models\User;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\User\QMUser;

/** Class ImportVariablesSpreadsheetTest
 * @package App\PhpUnitJobs
 */
class EconomicImportJob extends JobTestCase {
    public function testImportLifeExpectancy(){
        $data = CsvFile::readCsv('tmp/qm-static-data/life-expectancy.csv');
        $u = QMUser::getOrCreate([
            User::FIELD_ID => UserIdProperty::USER_ID_THINK_BY_NUMBERS,
            User::FIELD_USER_EMAIL => "system@thinkbynumbers.org",
            User::FIELD_DISPLAY_NAME => "Think by Numbers",
        ]);
        foreach($data as $point){
            $v = $u->getOrCreateQMUserVariable($point->country." Life Expectancy");
        }
    }
}
