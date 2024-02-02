<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\DataSources\QMConnector;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\NoFileChangesException;
use App\Files\FileHelper;
use App\Intents\QMIntent;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Variable;
use App\Models\WpLink;
use App\Repos\ApplicationSettingsRepo;
use App\Repos\IonicRepo;
use App\Repos\StaticDataRepo;
use App\Slim\Model\Phrases\Phrase;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\QMUnit;
use App\Slim\Model\States\IonicState;
use App\Storage\QMFileCache;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Variables\QMVariableCategory;
use App\Variables\VariableSearchResult;
class StaticAppData extends QMResponseBody {
    public $appSettings;
    public $buildInfo;
    public $chcp;
    public $chromeExtensionManifest;
    /**
     * @var VariableSearchResult[]
     */
    public $commonVariables;
    public $configXml;
    public $connectors;
    public $deepThoughts;
    public $dialogAgent;
    public $docs;
    public $privateConfig;
    public $states;
    public $units;
    public $variableCategories;
    public $stateNames;
    private $clientId;
    /**
     * StaticAppData constructor.
     * @param string $clientId
     * @param AppSettings|null $appSettings
     */
    public function __construct(string $clientId, AppSettings $appSettings = null){
        parent::__construct();
        $this->clientId = $clientId;
        $this->stateNames = IonicState::getStateNamesMap();
        if(AppMode::isApiRequest()){
            $searchResults = QMFileCache::get(__METHOD__."-variables");
        } else {
            $searchResults = null;
        }
        if(!$searchResults){
            $commonVariables = Variable::query()
                // They're all zero in the test DB ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 3)
                ->orderBy(Variable::FIELD_NUMBER_OF_USER_VARIABLES, 'desc')
                ->select(Variable::getImportantColumns())
                ->limit(500)
                ->get();
            $searchResults = VariableSearchResult::toDBModels($commonVariables);
            QMArr::assertNotNull($searchResults, 'predictor');
            QMArr::assertNotNull($searchResults, 'outcome');
            $searchResults = QMArr::unsetNullPropertiesOfObjectsInArray($searchResults);
            QMFileCache::set(__METHOD__."-variables", $searchResults);
        }
        try {
            $this->appSettings = $appSettings = $appSettings ?? Application::getByClientId($clientId);
        } catch (ClientNotFoundException $e) {
            le($e);
        }
        $this->commonVariables = $searchResults;
        $connectors = QMConnector::getUnauthenticated();
        $connectors = QMStr::replaceLocalUrlsWithProduction($connectors);
        $this->connectors = $connectors;
        $this->deepThoughts = Phrase::getDeepThoughts();
        $this->dialogAgent = QMIntent::getAgent();
        $this->docs = SwaggerJson::get();
        $this->states = IonicState::getStatesJson();
        $this->units = QMUnit::getUnits();
        $this->variableCategories = QMVariableCategory::get();
        //FileHelper::writeJsonFile('data','staticAppData', $this);  // TODO: What is this for?
    }
    /**
     * @return void
     */
    public function writeAllDataTypesToFiles(): void {
        $this->writeCommonVariablesToFile();
        $this->writeConnectorsToFile();
        $this->writeDeepThoughtsToFile();
        $this->writeDialogAgentToFile();
        $this->writeDocsToFile();
        $this->writeStateNamesToFile();
        $this->writeStatesToFile();
        $this->writeUnitsToFile();
        $this->writeVariableCategoriesToFile();
        $this->writeJson('links', WpLink::generateLinks()->toArray());
    }
    /**
     * @return bool
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function writeAppSettingsToLocalFile(): bool{
        $json = $this->toJson();
        $path = ApplicationSettingsRepo::getAbsolutePath('apps');
        /** @noinspection PhpUnhandledExceptionInspection */
        $changed = FileHelper::writeJsonFile($path, $json, $this->clientId);
        if(!$changed){return $changed;}
        /** @noinspection PhpUnhandledExceptionInspection */
        $changed = FileHelper::writeByDirectoryAndFilename($path, $this->clientId.'.js', 'var qm.staticData.appSettings = '.$json.';');
        return $changed;
    }
    /**
     * @return bool
     */
    public function postAppSettingsToGithubAPIAndWriteToLocalRepo(): bool {
        $json = $this->toJson();
        try {
            StaticDataRepo::updateOrCreateByAPI('apps', $this->clientId.'.json',
                $json, $this->clientId.'.json');
            StaticDataRepo::updateOrCreateByAPI('apps', $this->clientId.'.js',
                'var qm.staticData.appSettings = '.$json.';', $this->clientId.'.js');
            $changed = true;
        } catch (NoFileChangesException $e) {
            QMLog::infoWithoutContext(__METHOD__.": ".$e->getMessage());
            $changed = false;
        }
        return $changed;
    }
    public function writeCommonVariablesToFile(){
        $this->writePropertyToFile('commonVariables', [
            '"predictor": true,',
            '"outcome": true,',
            '"predictor": false,',
            '"outcome": false,',
        ]);
    }
    public function writeUnitsToFile(){
        $this->writePropertyToFile('units');
    }
    public function writeConnectorsToFile(){
        $this->writePropertyToFile('connectors');
    }
    public function writeDialogAgentToFile(){
        $this->writePropertyToFile('dialogAgent');
    }
    public function writeDeepThoughtsToFile(){
        $this->writePropertyToFile('deepThoughts');
    }
    public function writeDocsToFile(){
        $this->writePropertyToFile('docs');
    }
    public function writeStatesToFile(){
        $this->writePropertyToFile('states');
    }
    public function writeVariableCategoriesToFile(){
        $this->writePropertyToFile('variableCategories');
    }
    public function writeStateNamesToFile(){
        $this->writePropertyToFile('stateNames');
    }
    /**
     * @param string $name
     * @param array $requiredStrings
     */
    public function writePropertyToFile(string $name, array $requiredStrings = []){
        $json = $this->writeJson($name, $this->$name);
        $this->writeJsFile($json, $requiredStrings, $name);
    }
    /**
     * @return false|mixed|string
     */
    private function toJson(){
        $clone = clone $this;
        $json = QMStr::prettyJsonEncode($clone);
        $json = QMStr::replaceLocalUrlsWithProduction($json);
        return $json;
    }
    /**
     * @param string $name
     * @param $data
     * @return string
     */
    public function writeJson(string $name, $data): string{
        $json = QMStr::prettyJsonEncode($data);
        try {
            StaticDataRepo::writeJsonFile($name, $json);
        } catch (InvalidFilePathException $e) {
            le($e);
        }
        try {
            IonicRepo::writeJsonFile("src/data/".$name, $json);
        } catch (InvalidFilePathException $e) {
            le($e);
        }
        return $json;
    }
    /**
     * @param string $json
     * @param array $requiredStrings
     * @param string $name
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function writeJsFile(string $json, array $requiredStrings, string $name): void{
        /** @noinspection PhpUnhandledExceptionInspection */
        QMValidatingTrait::assertStringContains($json, $requiredStrings, $name);
        $js = 'if(typeof qm === "undefined"){if(typeof window === "undefined") {global.qm = {}; }else{window.qm = {};}}
if(typeof qm.staticData === "undefined"){qm.staticData = {};}
qm.staticData.'.$name.' = '.$json.';';
        StaticDataRepo::writeToFile($name.'.js', $js);
        IonicRepo::writeToFile("src/data/".$name.'.js', $js);
    }
}
