<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Files\FileHelper;
use App\Files\PHP\VariableCategoryFile;
use App\Logging\QMLog;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\QMCommonVariable;
use const LIBXML_COMPACT;
use const LIBXML_PARSEHUGE;
class HardCodingJob extends JobTestCase {
	public function testGenerateHardCodedVariableCategories(){
		VariableCategoryFile::updateAll();
	}
}
