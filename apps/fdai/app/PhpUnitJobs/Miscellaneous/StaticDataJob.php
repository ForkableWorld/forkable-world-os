<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Miscellaneous;
use App\Buttons\GithubButton;
use App\Buttons\IonicButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RouteButton;
use App\Charts\QMHighcharts\HighchartConfig;
use App\DataSources\QMConnector;
use App\Files\PHP\BaseModelFile;
use App\Menus\Admin\AdminSearchMenu;
use App\Menus\QMMenu;
use App\Menus\UserMenu;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\CtTreatment\CtTreatmentNumberOfConditionsProperty;
use App\Repos\ApplicationSettingsRepo;
use App\Repos\CCStudiesRepo;
use App\Repos\ImagesRepo;
use App\Repos\IonicRepo;
use App\Repos\QMAPIRepo;
use App\Repos\StaticDataRepo;
use App\Repos\StudiesRepo;
use App\Storage\DB\Migrations;
use App\Storage\DB\TestDB;
use App\UI\CssHelper;
use App\UI\JSHelper;
use App\Utils\Env;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PrecipitationCommonVariable;
class StaticDataJob extends JobTestCase {
	public function testStaticDataJob(){
		StaticDataRepo::updateAndCommitStaticData();
    }
    public function testGenerateIndexFiles(){
        BaseModel::generateIndexFiles();
    }
    public function testPublishVariables(){
        $correlation = GlobalVariableRelationship::find(65684711);
        $correlation->logName();
        CCStudiesRepo::cloneOrPullIfNecessary();
        //GlobalVariableRelationshipIsPublicProperty::updateAll();
        //CCStudiesRepo::publishVariables();
        CCStudiesRepo::publishVariable(PrecipitationCommonVariable::NAME);
    }
    public function testUploadHighchartThemes(){
        HighchartConfig::uploadThemes();
    }
    public function testGenerateStaticStudies(){
        CtTreatmentNumberOfConditionsProperty::updateAll();
        StudiesRepo::testPublish();
    }
    public function testUpdateAppSettings(){
        //ApplicationSettingsRepo::addAllCommitAndPush("untracked");
        //ApplicationSettingsRepo::checkForSecrets();
        ApplicationSettingsRepo::updateAndCommitAppSettings();
    }
    public function testGenerateMenus(){
        AdminSearchMenu::saveMaterialStatCards();
        UserMenu::saveMaterialStatCards();
    }
    public function testUploadIonic(){
        //StudyImages::uploadStudyImages();
        IonicRepo::uploadToS3Public(true);
    }
    public function testUploadStudyImages(){
        //StudyImages::uploadStudyImages();
        ImagesRepo::uploadToS3Public();
    }
    public function testGenerateEverything(){
        //WpLink::generateProperties();
        StaticDataRepo::updateAndCommitStaticData();
        // not sure what this was for? BaseHighstock::generateExamples();
        QMConnector::exportClientIdsAndSecretsForService();
        GithubButton::generate();
        RouteButton::generateDevRouteButtons();
        IonicButton::generateIonicStateButtons();
        Env::alphabetizeEnvFiles();
        //Heroku::updateEnvFiles();
        RelationshipButton::saveAndCommitHardCodedRelationships();
        BaseModelFile::updatePHPDocs();
        BaseModelFile::generateModels();
        //QMAPIRepo::generateAndCommitLaravelDocs();
        QMAPIRepo::generateOpenApiSpec();
        TestDB::updateAndCommitTestDB();
        StaticDataJob::countMigrations();
        //DocsRepo::updateAndCommitZiggy();
        // I think we need a separate job for this TestDB::updateAndCommitTestDB();
	    QMMenu::generateAndCommitMenusAndButtons();
	    ApplicationSettingsRepo::updateAndCommitAppSettings();
    }
    public function testUploadCSSFiles(){
        CssHelper::uploadCss();
    }
    public function testUploadJSFiles(){
        JSHelper::uploadJSFiles(true);
    }
    protected static function countMigrations(): void{
        QMAPIRepo::createFeatureBranch("count-number-of-migration");
        Migrations::generateNumberOfCountMigrations();
        QMAPIRepo::addFilesInFolder("database/migrations");
        QMAPIRepo::commitAndPush("Created count migrations");
    }
}
