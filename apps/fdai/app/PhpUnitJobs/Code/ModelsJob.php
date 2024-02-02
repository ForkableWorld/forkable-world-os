<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Files\FileHelper;
use App\Files\PHP\BaseModelFile;
use App\Models\User;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Repos\LaravelModelGeneratorRepo;
use App\Storage\DB\TdddDB;
class ModelsJob extends JobTestCase {
	public function testUpdateLaravelModelGeneratorRepo(){
		LaravelModelGeneratorRepo::syncFromVendorAndCommit();
	}
	public function testReformatModels(){
		BaseModelFile::reformatAll();
	}
	public function testUpdateModels(){
		User::generateProperties();
		BaseModelFile::generateModels();
		//ModelFile::updateExisting();
	}
	public function testRenameClass(){
		//ClassFile::replaceInClassNames("BO", "OA");
		FileHelper::renameProjectFilesStartingWith("oa_", "oa_");
	}
	public function testGenerateModelTraits(){
		Variable::generateTrait();
	}
	public function testGenerateModel(){
		//BaseModelFile::generateByTable('jenkins_slaves', TdddDB::CONNECTION_NAME);
		//BaseModelFile::updatePHPDocs();
	}
}
