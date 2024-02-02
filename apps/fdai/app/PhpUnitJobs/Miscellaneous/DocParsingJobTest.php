<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Miscellaneous;
use App\Utils\APIHelper;
use App\Files\FileHelper;
use App\Types\QMStr;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs
 */
class DocParsingJobTest extends JobTestCase {
    public function testDocParsingJob(){
        $filename = FileHelper::absPath('15P0563 troxel.doc');
        //$text = DocumentParser::parseFromFile($filename);
        $contents = mb_convert_encoding(file_get_contents($filename), 'utf8', 'binary');
        $subjects = $methods = $standards = [];
        $subject = trim(QMStr::between($contents, "SUBJECT:","STANDARDS:"));
        if(!isset($subjects[$subject])){$subjects[$subject] = 1;} else {$subjects[$subject]++;}
        $standard = trim(QMStr::between($contents, "STANDARDS:","TEST METHODS:"));
        if(!isset($standards[$standard])){$standards[$standard] = 1;} else {$standards[$standard]++;}
        $method = trim(QMStr::between($contents, "TEST METHODS:","UNITS:"));
        if(!isset($methods[$method])){$methods[$method] = 1;} else {$methods[$method]++;}
    }
    private function getFileList(){

    }
}
