<?php
namespace Tests\Traits;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\SecretException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\TestArtifacts\TestLogsFile;
use App\Storage\S3\S3Private;
use App\Types\QMStr;
use Facade\Ignition\LogRecorder\LogMessage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use PHPUnit\Runner\BaseTestRunner;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Tests\QMBaseTestCase;
trait LogsTests
{
	protected ?array $logs = [];
    public static function getDuration(): float {
        return QMBaseTestCase::getTestDuration();
    }
    /**
     * @return array
     */
    public function getLogs(): array{
        return $this->logs;
    }
    /**
     * @param LogMessage $logs
     */
    public function addLog(LogMessage $logs): void{
        $this->logs[] = $logs;
    }
    /**
     * @return bool|string
     */
    public function uploadResults(){
        if($this->isSuccessful()){
            try {
                return S3Private::upload($this->getLogFilePath(), $this->getLogs());
            } catch (SecretException | MimeTypeNotAllowed $e) {
               le($e);
            }
        } else {
            $this->compareLogsWithLastSuccessful();
        }
    }
    public function isSuccessful(): bool {
        return !$this->hasFailed() && $this->getStatus() !== BaseTestRunner::STATUS_UNKNOWN;
    }
    public function getTestArtifactPrefix(): string{
        return $this->getFolder().
            DIRECTORY_SEPARATOR.
            $this->getShortClass().
            '-'.
            $this->getName().
            '-';
    }
    public function getFolder():string{
        return FileHelper::getFolderFromFilePath($this->getPathAndLineToTest());
    }
    /**
     * @return string
     */
    public function getClass(): string{
        return get_class($this);
    }
    /**
     * @return string
     */
    public function getShortClass(): string{
        return QMStr::toShortClassName(self::getClass());
    }
    public function getPathAndLineToTest(): string {
        try {
            return FileFinder::getPathAndLineToTest($this);
        } catch (QMFileNotFoundException $e) {
            le($e);
        }
    }
	public function getPathToTest(): string {
		try {
			return FileFinder::getPathToTest($this);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
    /**
     * @throws QMFileNotFoundException
     */
    private function compareLogsWithLastSuccessful(){
	    $logs = $this->getLastSuccessfulLogs();
	    TestLogsFile::assertSameStringInFile(TestLogsFile::generatePath(), $logs);
    }
    /**
     * @return false|string
     */
    private function getLastSuccessfulLogs(): string {
        try {
            return S3Private::get($this->getLogFilePath());
        } catch (FileNotFoundException $e) {
            le($e);
        }
    }
    /**
     * @return string
     */
    public function getLogFilePath(): string{
        return $this->getTestArtifactPath("logs.txt");
    }
    /**
     * @param string $suffix
     * @return string
     */
    public function getTestArtifactPath(string $suffix): string{
        return $this->getTestArtifactPrefix().$suffix;
    }
}
