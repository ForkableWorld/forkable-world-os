<?php
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Http\Controllers;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Exceptions\UnexpectedStatusCodeException;
use App\Models\User;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Models\WpTermRelationship;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Traits\UsesMinIOServer;
use App\Utils\Env;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\UnitTestCase;
use Tests\UnitTests\Posts\UserOverviewProfilePostTest;
/**
 * @coversDefaultClass \App\Http\Controllers\FileUploadController
 */
class FileUploadTest extends UnitTestCase {
    //use UsesMinIOServer;
	/**
	 * @var int
	 */
	private int $userId = 2;
	public const DISABLED_UNTIL      = UserOverviewProfilePostTest::DISABLED_UNTIL;
	public const REASON_FOR_SKIPPING = UserOverviewProfilePostTest::REASON_FOR_SKIPPING;
	protected function setUp():void{
		$this->skipIfNotLocal('Cannot reproduce locally');
        parent::setUp();
        // Not sure why but the boot events don't fire unless I do this
        // https://github.com/laravel/framework/issues/1181
//        WPAttachment::flushEventListeners(); // add this to remove all event listeners
//        WPAttachment::registerEventListeners();  // reboot the static to reattach listeners
//        WPAttachment::boot();
        WpPostmetum::query()->forceDelete();
        WpTermRelationship::query()->forceDelete();
        WpPost::query()->forceDelete();
        //$this->bootUsesMinIOServer();
    }
	/**
	 * @throws FileNotFoundException
	 */
	public function testPrivateFileUploadDownloadDelete(){
        if($this->weShouldSkip()){return;}
        $nameParam = 'different-name.jpg';
        $folderParam = "test-path";
        $response = $this->generateFakeImageAndUpload($nameParam, $folderParam);
        $this->checkPrivateImageDownloadUrl($response, $nameParam, $folderParam);
    }
	/**
	 * @throws FileNotFoundException
	 */
	public function testPrivateFileUploadDownloadDeleteWithoutName(){
        if($this->weShouldSkip()){return;}
        Storage::fake('avatars');
        $this->actingAsUserId($this->userId);
        $generatedFileName = 'file-name.jpg';
        $file = UploadedFile::fake()->image($generatedFileName);
        $response = $this->json('POST', 'api/v2/file', [
            'file' => $file,
        ]);
        $this->checkPrivateImageDownloadUrl($response, $generatedFileName);
    }
	/**
	 * @throws UnexpectedStatusCodeException
	 */
	public function testPrivateFileUploadWithoutAuthentication(){
        $this->skipTest("TODO");
        Storage::fake('avatars');
        QMAuth::logout(__FUNCTION__);
        $name = 'different-name.jpg';
        $file = UploadedFile::fake()->image('file-name.jpg');
        $folder = "test-path";
		self::expectException(AuthenticationException::class);
        $response = $this->json('POST', 'api/v2/file', [
            'file' => $file,
            'name' => $name,
            "folder" => $folder
        ]);
        $content = $response->getContent();
        $this->assertContains("Unauthorized", $content);
        $this->assertStatusCodeEquals(401, $response, 'api/v2/file', 'POST');
    }
	/**
	 * @param TestResponse $response
	 * @param string $nameParamOrOriginalName
	 * @param string|null $folderParam
	 * @throws FileNotFoundException
	 */
    private function checkPrivateImageDownloadUrl(TestResponse $response,
                                                  string $nameParamOrOriginalName,
                                                  string $folderParam = null): void{
        $content = json_decode($response->getContent());
        $url = $content->url;
        $url = QuantiModoConnector::convertToTestUrl($url);
        //$this->checkWpPost($url);
        $this->checkDownloadUrl($url, $nameParamOrOriginalName, $folderParam);
        $this->assertInList($nameParamOrOriginalName, $folderParam);
        $this->makeSureUserCanGetFileDirectlyByUrl($url);
        $this->makeSureUserCanGetFileDirectlyByFolderAndName($nameParamOrOriginalName, $folderParam);
        $this->makeSureWeCannotGetFromStagingWithoutAuthentication($url);
		if(time() > time_or_exception("2021-12-15")){
			$this->makeSureWeCanDeleteFileByUrl($url);
			$this->assertNotInList($nameParamOrOriginalName, $folderParam);
		}
    }
    /**
     * @param string $url
     * @param string $nameParamOrOriginalFileName
     * @param string|null $folderParam
     */
    private function checkDownloadUrl(string $url, string $nameParamOrOriginalFileName, string $folderParam = null): void{
        $this->assertStringContainsString($nameParamOrOriginalFileName, $url);
        $this->assertStringContainsString("http", $url);
        if($folderParam){
            $this->assertStringContainsString($folderParam, $url);
        }
    }
    /**
     * @param string $url
     * @return void
     * @throws FileNotFoundException
     */
    private function makeSureUserCanGetFileDirectlyByUrl(string $url): void{
        $u = User::findInMemoryOrDB($this->userId);
        $image = $u->getFileDataByUrl($url);
        $this->assertStringContainsString("JPEG", $image);
    }
    /**
     * @param string $name
     * @param string|null $folder
     * @return void
     * @throws FileNotFoundException
     */
    private function makeSureUserCanGetFileDirectlyByFolderAndName(string $name, string $folder = null): void{
        $u = User::findInMemoryOrDB($this->userId);
        $image = $u->getFileDataByNameAndFolder($name, $folder);
        $this->assertStringContainsString("JPEG", $image);
    }
	/**
	 * @param string $url
	 */
    private function makeSureWeCannotGetFromStagingWithoutAuthentication(string $url): void{
        $this->logout();
        $this->assertGuest();
		$url = str_replace(Env::getAppUrl()."/", "", $url);
        $response = $this->get($url);
        if($response->isRedirect()){return;}
		$content = $response->getContent();
		$code = $response->getStatusCode();
		$this->assertEquals("", $content,
		                      "We should not be able to access $url on staging server without authentication but we got: "
           );
    }
    /**
     * @param string $url
     * @throws FileNotFoundException
     */
    private function makeSureWeCanDeleteFileByUrl(string $url): void{
        $u = User::findInMemoryOrDB($this->userId);
        $u->deleteFile($url);
        $this->expectException(FileNotFoundException::class);
        $u->getFileDataByUrl($url);
        $post = WpPost::whereGuid($url)->first();
        $this->assertNull($post);
    }
    /**
     * @param File $file
     * @param string $nameParam
     * @param string $folderParam
     * @return TestResponse
     */
    private function uploadFile(File $file, string $nameParam, string $folderParam): TestResponse{
        $response = $this->json('POST',
            'api/v2/file',
            [
                'file'   => $file,
                'name'   => $nameParam,
                "folder" => $folderParam
            ]);
        return $response;
    }
    /**
     * @param string $nameParam
     * @param string $folderParam
     * @return TestResponse
     */
    private function generateFakeImageAndUpload(string $nameParam, string $folderParam): TestResponse{
        $this->actingAsUserId(2);
        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('file-name.jpg');
        $response = $this->uploadFile($file, $nameParam, $folderParam);
        return $response;
    }
    /**
     * @param string $nameParamOrOriginalName
     * @param string|null $folderParam
     */
    private function assertInList(string $nameParamOrOriginalName, string $folderParam = null){
        $u = User::findInMemoryOrDB($this->userId);
        $list = $u->listFilesOnS3();
        $this->assertArrayContainsItemContainingString(
            $this->getFilePath($nameParamOrOriginalName, $folderParam),
            $list);
    }
    /**
     * @param string $nameParamOrOriginalName
     * @param string $folderParam
     */
    private function assertNotInList(string $nameParamOrOriginalName, string $folderParam){
        $u = User::findInMemoryOrDB(1);
        $list = $u->listFilesOnS3();
        $this->assertArrayDoesNotContainItemContainingString(
            $this->getFilePath($nameParamOrOriginalName, $folderParam),
            $list);
    }
    /**
     * @param string $needle
     * @param $array
     */
    public function assertArrayContainsItemContainingString(string $needle, $array):void {
        $result = Arr::first($array, function($haystack)use($needle){
            return strpos($haystack, $needle) !== false;
        });
        if(!$result){
           le($needle." not found in ".\App\Logging\QMLog::print_r($array, true));
        }
        \App\Logging\ConsoleLog::info("$result contains $needle");
    }
    /**
     * @param string $needle
     * @param $array
     */
    public function assertArrayDoesNotContainItemContainingString(string $needle, $array):void {
        $result = Arr::first($array, function($haystack)use($needle){
            return strpos($haystack, $needle) !== false;
        });
        if($result){
            throw new \LogicException($needle." found in ".\App\Logging\QMLog::print_r($array, true));
        }
    }
    /**
     * @param string $nameParamOrOriginalName
     * @param string|null $folderParam
     * @return string
     */
    private function getFilePath(string $nameParamOrOriginalName, string $folderParam = null): string{
        $u = QMUser::find($this->userId);
        if($folderParam){
            return "users/{$u->getSlug()}/".$folderParam."/".$nameParamOrOriginalName;
        }
        return "users/{$u->getSlug()}/".$nameParamOrOriginalName;
    }
}
