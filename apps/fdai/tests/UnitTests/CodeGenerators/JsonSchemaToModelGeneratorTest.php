<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\CodeGenerators;
use App\DevOps\Jenkins\JenkinsAPI;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\Json\JsonSchemaToPhpFile;
use App\Types\QMArr;
use Tests\UnitTestCase;
class JsonSchemaToModelGeneratorTest extends UnitTestCase
{
    private $file;
    public function testToJsonSchemaToPhp(){
		$this->skipTest("Don't need this shit");
        //$this->saveJenkinsComputerResponse();
        $this->deleteSchemaAndModels();
        $this->generateJsonSchema();
        $this->generateModel();
        $this->deleteSchemaAndModels();
    }
    private function getTestJson(): string {
        try {
            return FileHelper::getContents($this->getInputDataPath());
        } catch (QMFileNotFoundException $e) {
            le($e);
        }
    }
    /**
     * @return void
     */
    private function deleteSchemaAndModels(): void{
        $schema = $this->getJsonSchemaFile();
        $this->assertSameRelativePath($schema->getPhpOutputFolder(),
            "app/Generated/JsonSchemaTest/TestJsonSchema");
        $schema->deletePhpOutputFolder();
        $this->assertFileDoesNotExist($schema->getPhpOutputFolder());
        $schema->delete("testing");
        $this->assertPathContains("resources/json-schema/app/Generated/JsonSchemaTest", $schema->getFolderPath());
    }
    private function getJsonSchemaFile(): JsonSchemaToPhpFile  {
        if($this->file){return $this->file;}
        return $this->file = new JsonSchemaToPhpFile("resources/json-schema/app/Generated/JsonSchemaTest/TestJsonSchema.json");
    }
    private function generateJsonSchema(): void{
        $schema = $this->getJsonSchemaFile();
        $schema->delete("testing");
        $this->assertFileDoesNotExist($schema->getPath());
        $data = $this->getTestJson();
        $data = json_decode($data);
        $schema->dataToSchema($data);
        $this->assertFileExists($schema->getPath());
    }
    private function generateModel(): void{
        $schema = $this->getJsonSchemaFile();
        $this->assertFileDoesNotExist($schema->getPhpOutputFolder());
        $schema->generatePHP();
        $this->assertFileExists($schema->getPhpOutputFolder());
    }
    private function getInputDataPath(): string{
        return "resources/json-responses/tests/UnitTests/CodeGenerators/TestJsonSchema.json";
    }
    private function getInputJsonName(): string{
        return QMArr::last(explode("/", $this->getInputDataPath()));
    }
    private function saveJenkinsComputerResponse(): void{
        $data = json_decode(JenkinsAPI::curl('/computer/api/json'));
        FileHelper::writeJsonFile($this->getInputDataPath(), $data->computer[29]);
    }

}
