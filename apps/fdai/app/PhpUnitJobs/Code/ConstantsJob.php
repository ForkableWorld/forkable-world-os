<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\DataSources\SpreadsheetImportRequest;
use App\Files\Bash\BashScriptFile;
use App\Files\FileHelper;
use App\Folders\DynamicFolder;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Repos\ImagesRepo;
use App\Repos\HomesteadRepo;
use App\PhpUnitJobs\JobTestCase;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\UI\Markdown;
class ConstantsJob extends JobTestCase
{
	public function testReplaceSlimConstants(){
		$lClasses = BaseModel::getClasses();
		foreach($lClasses as $LClass){
			if(method_exists($LClass, "getSlimClass")){
				$slimClass = $LClass::getSlimClass();
				$constants = $LClass::getConstants();
				foreach($constants as $name => $val){
					if(defined("$slimClass::$name")){
						$shortSlim = QMStr::toShortClassName($slimClass);
						FileHelper::replaceInProjectFiles($shortSlim."::".$name, "$LClass::$name");
					}
				}
			}
		}
	}
	public function testMarkdownConstants(){
		Markdown::updateConstants();
	}
	public function testIsTemporalTraits(){
		BaseProperty::addIsTemporalTraits();
		BaseProperty::replaceColumnStringsWithConstants();
	}
    public function testOutputConstants(){
	    BashScriptFile::outputConstants();
    	HomesteadRepo::outputConstants();
    	DynamicFolder::outputConstants();
        ImagesRepo::outputConstantsForFolder('programming');
    }
	public function testGenerateConstantsFromDBFields() {
		Writable::outputTableConstants(SpreadsheetImportRequest::TABLE);
	}
}
