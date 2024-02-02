<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Utils\GeoLocation;
use App\Types\QMArr;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUnitCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use App\PhpUnitJobs\JobTestCase;
use Riimu\Kit\PHPEncoder\PHPEncoder;
use Throwable;

/** @package App\PhpUnitJobs
 */
class ModelGeneratorJob extends JobTestCase {
    public static $variableNamesToGenerate = [];
    public function testUpdateVariableModels(){
        $constants = QMCommonVariable::getHardCodedVariables();
        foreach($constants as $v){
            $cv = QMCommonVariable::findByName($v->name);
            $cv->generateChildModelCode();
        }
    }
    public function testGenerateGeolocationModel(){
        $location = GeoLocation::ipData("24.216.163.200");
        $this->createParentModelFile("GeoLocation", $location);
        $this->createParentModelFile("Currency", $location->currency);
        $this->createParentModelFile("TimeZone", $location->time_zone);
    }
    public function testGenerateUnitModels(){
        foreach(QMUnit::getUnits() as $unit){
            ModelGeneratorJob::createChildModelFile("Unit", $unit);}
    }
    public function testGenerateVariableCategoryModels(){
        foreach(QMVariableCategory::get() as $category){
            ModelGeneratorJob::createChildModelFile("VariableCategory", $category);
        }
    }
    public function testGenerateUnitCategoryModels(){
        foreach(QMUnitCategory::getAsArray() as $category){
            ModelGeneratorJob::createChildModelFile("UnitCategory", $category);}
    }
    /**
     * @param string $parentClassName
     * @param $data
     * @param string|null $subFolder
     * @param string|null $categoryName
     */
    public static function createChildModelFile(string $parentClassName,
                                                $data,
                                                string $subFolder = null,
                                                string $categoryName = null) {
        $pluralParentClass = QMStr::pluralize($parentClassName);
        if(is_object($data)){
            $name = $data->name;
        } else {
            $name = $data['display_name'] ?? $data['name'];
        }
        $childClassName = ModelGeneratorJob::getChildClassName($parentClassName, $name);
        $content = ModelGeneratorJob::addChildModelHeader($parentClassName, $name, $subFolder);
        $content = ModelGeneratorJob::addPropertiesWithValues($content, $data);
        $content .= '}';
        $directory = 'Api/Model//';
        if ($subFolder) {
            $directory .= $subFolder . '//';
        }
        $directory = $directory . $pluralParentClass;
        if ($categoryName) {
            $directory = $directory . DIRECTORY_SEPARATOR . $categoryName;
        }
        $directory = FileHelper::absPath($directory);
        FileHelper::createDirectoryIfNecessary($directory);
        FileHelper::writeByDirectoryAndFilename($directory, $childClassName . '.php', $content);
    }
    /**
     * @param string $parentClassName
     * @param $data
     * @param string|null $subFolder
     * @param bool $includePropertyValues
     */
    private function createParentModelFile(string $parentClassName, $data, string $subFolder = null, bool $includePropertyValues = true){
        $content = $this->addParentModelHeader($parentClassName, $subFolder);
        $content = $this->addPropertiesWithoutValues($content, $data);
        $content .= '}';
        $directory = 'Api/Model//';
        if($subFolder){
            $directory .= $subFolder.'//';
        }
        $directory = FileHelper::absPath($directory);
        FileHelper::createDirectoryIfNecessary($directory);
        FileHelper::writeByDirectoryAndFilename($directory, $parentClassName . '.php', $content);
    }
    /**
     * @param array $connectorData
     */
    private function addConnectorProperties(array $connectorData){
        $directory = FileHelper::absPath('app/DataSources/Connectors');
        $childClassName = str_replace(" ", '', ucwords($connectorData['display_name']))."Connector";
        $filePath = "$directory/$childClassName.php";
        try {
            $existingFile = file_get_contents($filePath);
        } catch (Throwable $e) {
            QMLog::infoWithoutContext(__METHOD__.": ".$e->getMessage());
            return;
        }
        $prefix = QMStr::before("{", $existingFile) .'{'.PHP_EOL;
        $suffix = QMStr::after('{', $existingFile);
        $content = ModelGeneratorJob::addPropertiesWithValues($prefix, $connectorData);
        $content .= $suffix;
        FileHelper::createDirectoryIfNecessary($directory);
        FileHelper::writeByDirectoryAndFilename($directory, $childClassName . '.php', $content);
    }
    /**
     * @param string $parentClassName
     * @param string $childName
     * @param string|null $subFolder
     * @return string
     */
    public static function addChildModelHeader(string $parentClassName,
                                               string $childName,
                                               string $subFolder = null): string {
        $childClassName = ModelGeneratorJob::getChildClassName($parentClassName, $childName);
        $pluralParentClass = QMStr::pluralize($parentClassName);
        $content = '<?php' . PHP_EOL ;
        if($subFolder){
            $nameSpace = 'App\Slim\Model\\'.$subFolder.'\\'.$pluralParentClass;
        } else{
            $nameSpace = 'App\Slim\Model\\'.$pluralParentClass;
        }
        $content .= 'namespace '.$nameSpace.';' . PHP_EOL ;
        if($subFolder){
            $content .= 'use App\Slim\Model\\'.$subFolder.'\\'.$parentClassName.';' . PHP_EOL ;
        } else {
            $content .= 'use App\Slim\Model\\'.$parentClassName.';' . PHP_EOL ;
        }
        $content .= 'class ' . $childClassName . ' extends ' . $parentClassName . ' {' . PHP_EOL;
        return $content;
    }
    /**
     * @param string $parentClassName
     * @param string $childName
     * @param string|null $subFolder
     * @return string
     */
    private function addParentModelHeader(string $parentClassName, string $subFolder = null): string {
        $content = '<?php' . PHP_EOL ;
        $content .= 'namespace App\Slim\Model'.';' . PHP_EOL ;
        $content .= 'class ' . $parentClassName . ' {' . PHP_EOL;
        return $content;
    }
    /**
     * @param string $content
     * @param $data
     * @return string
     */
    public static function addPropertiesWithValues(string $content, $data): string {
        $encoder = new PHPEncoder();
        $data = QMArr::alphabetizeObjectToArray($data);
        foreach($data as $key => $value){
            //if($value === null){continue;}
            if(is_array($value)){
                foreach($value as $subKey => $subValue){
                    if(is_object($subValue)){
                        $value[$subKey] = json_decode(json_encode($subValue), true);
                    }
                }
            }
            $constName = QMStr::toScreamingSnakeCase($key);
            if (is_string($value) && strpos($value, "::") !== false) {
                $content .= QMStr::tab() .
                    "public const $constName = " .
                    $value .
                    ';' .
                    PHP_EOL;
            } else {
                $content .= QMStr::tab() .
                    "public const $constName = " .
                    $encoder->encode($value) .
                    ';' .
                    PHP_EOL;
            }
        }
        foreach($data as $key => $value){
            if($value === null){continue;}
            $constName = QMStr::toScreamingSnakeCase($key);
            $camel = QMStr::camelize($key);
            $content .= QMStr::tab(). "public $$camel = self::$constName;" . PHP_EOL;
        }
        return $content;
    }
    /**
     * @param string $content
     * @param $data
     * @return string
     */
    private function addPropertiesWithoutValues(string $content, $data): string {
        $data = QMArr::alphabetizeObjectToArray($data);
        foreach($data as $key => $value){
            $camel = QMStr::camelize($key);
            $content .= QMStr::tab(). "public $$camel;" . PHP_EOL;
        }
        return $content;
    }
    /**
     * @param string $parentClassName
     * @param string $name
     * @return mixed|string
     */
    public static function getChildClassName(string $parentClassName, string $name) {
        $name = QMStr::before("spreadsheet", $name, $name);
        $childClassName = QMStr::toClassName($name);
        if(stripos($parentClassName, $childClassName) === 0){
            return $parentClassName;
        }
        $childClassName .= $parentClassName;
        return $childClassName;
    }
}
