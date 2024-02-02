<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpReturnDocTypeMismatchInspection */
/** @noinspection PhpUnused */
namespace App\Files;
use App\Buttons\Admin\PHPStormButton;
use App\Computers\ThisComputer;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\Bash\BashScriptFile;
use App\Files\Env\EnvFile;
use App\Files\Json\JsonFile;
use App\Files\PHP\PhpClassFile;
use App\Folders\DynamicFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Utils\AppMode;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;
use Nette\Utils\Strings;
use SplFileInfo;
use Symplify\SmartFileSystem\Exception\DirectoryNotFoundException;
use Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @package App\Files
 */
class UntypedFile extends File {
	use LoggerTrait;
	const DISK_BASE = 'base';
	/**
	 * @var FileLine[]
	 */
	protected ?array $lines = [];
	/**
	 * @var \SplFileInfo|null
	 */
	protected ?SplFileInfo $fileInfo = null;
	public string $absPath;
	/**
	 * @var string|null
	 */
	protected ?string $originalContent = null;
	/**
	 * @var string
	 * @see https://regex101.com/r/SYP00O/1
	 */
	private const LAST_SUFFIX_REGEX = '#\.[^.]+$#';
	/**
	 * @var mixed
	 */
	protected ?string $owner = null;
	/**
	 * @var SmartFileSystem
	 */
	private SmartFileSystem $smartFileSystem;
	/**
	 * @var array
	 */
	public static array $permissions = [
		'file' => [
			'public' => '0664',
			'private' => '0600',
		],
		'dir' => [
			'public' => '0755',
			'private' => '0700',
		],
	];
	/**
	 * @param null $file
	 */
	public function __construct($file = null){
		$file = $file ?? $this->getPath();
		$this->setAbsPath($file);
		parent::__construct($this->absPath, false);
		$this->smartFileSystem = new SmartFileSystem();
	}
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return static[]
	 */
	public static function getContaining(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): array{
		$paths = FileFinder::getFilesContaining($dir, $needle, $recursive, $filenameLike);
		$files = [];
		foreach($paths as $path){
			$files[(string)$path] = static::instantiate($path);
		}
		return $files;
	}
	/**
	 * @param string|SplFileInfo|static $path
	 * @return static|DynamicFolder
	 */
	public static function instantiate($path): self{
		if($path instanceof TypedProjectFile){
			return $path;
		}
		$map = [
			FileExtension::SH => BashScriptFile::class,
			FileExtension::PHP => PhpClassFile::class,
			FileExtension::JSON => JsonFile::class,
			FileExtension::TXT => TextFile::class,
		];
		if(strpos($path, "/.env") !== false){
			return new EnvFile($path);
		}
		$ext = self::pluckExtension($path);
		$class = $map[$ext];
		if(static::class === $class){
			return new static($path);
		}
		/** @var static $class */
		return $class::instantiate($path);
	}
	/**
	 * @param SplFileInfo[] $paths
	 * @return Collection|static[]
	 */
	public static function instantiateArray(array $paths): Collection{
		$files = [];
		foreach($paths as $path){
			$files[(string)$path] = static::instantiate($path);
		}
		return collect($files);
	}
	/**
	 * @param string $ext
	 * @param $path
	 * @return bool
	 */
	public static function extensionIs(string $ext, $path): bool{
		if(!Str::contains($ext, ".")){
			$ext = ".$ext";
		}
		return QMStr::endsWith($ext, $path);
	}
	/**
	 * @param $path
	 * @return string
	 */
	private static function pluckExtension($path): string{
		return FileHelper::getExtension($path);
	}
	/**
	 * @return string
	 */
	protected static function getDiskName(): string{
		return self::DISK_BASE;
	}

    /**
     * @param string|null $path
     * @throws InvalidFilePathException
     */
    public function setPath(string $path): void
    {
        $this->validatePath($path);
        $this->absPath = FileHelper::absPath($path);
    }

    /**
     * @param string $schemaPath
     * @throws InvalidFilePathException
     */
    protected function validatePath(string $schemaPath)
    {
        FileHelper::validateFilePath($schemaPath);
        $this->validateExtension($schemaPath);
    }

    /**
     * @param string $schemaPath
     */
    protected function validateExtension(string $schemaPath): void
    {
        $ext = static::getDefaultExtension();
        if (!UntypedFile::extensionIs($ext, $schemaPath)) {
            le("$schemaPath must end with .$ext");
        }
    }

    private function havePermissions(): bool{
		$owner = $this->getOwnerName();
		return $owner === ThisComputer::user();
	}
	/**
	 * @param string $destination
	 * @param string|null $perms
	 * @param string|null $owner
	 */
	public function copy(string $destination, string $perms = null, string $owner = null){
		if(!$perms){
			$perms = static::$permissions['file']['public'];
		}
		if(!$owner){
			$owner = ThisComputer::user();
		}
		$dstFile = new static($destination);
		FileHelper::createDirectoryIfNecessary(dirname($dstFile->getRealPath()));
		try {
			if(!copy($this->getRealPath(), $dstFile->getRealPath())){
				$errors = error_get_last();
				QMLog::error("could not copy file so trying with sudo because: " . \App\Logging\QMLog::print_r($errors, true));
				ThisComputer::sudoCopy($this->getPath(), $destination);
			}
		} catch (\Throwable $e) {
			QMLog::error("could not copy file so trying with sudo because: " . $e->getMessage());
			ThisComputer::sudoCopy($this->getPath(), $destination);
		}
		$dstFile->setPermissions($owner, $perms);
	}
	/**
	 * @return string
	 */
	public function getPath(): string{
		if($this->absPath){
			return $this->absPath;
		}
		if($this->fileInfo){
            $path = $this->getFileInfo()->getRealPath();
            $this->setPath($path);
            return $path;
		}
        $path = $this->getRealPath();
        $this->setPath($path);
        return $path;
	}
	/**
	 * @return string
	 */
	public function getFolderPath(): string{
		return FileHelper::getFolderFromFilePath($this->getPath());
	}
	/**
	 * @return string
	 */
	public function getFileName(): string{
		return FileHelper::getFileNameFromPath($this->getPath());
	}
	/**
	 * @param string $needle
	 * @return FileLine[]
     */
	public function getLinesStartingWith(string $needle, string $before = null): array{
		$lines = $this->getFileLines();
		$matches = [];
		foreach($lines as $line){
			if($before && str_contains($line->getString(), $before)){
				return $matches;
			}
			if($line->startsWith($needle)){
				$matches[$this->getPath() . ":" . $line->getNumber()] = $line;
			}
		}
		return $matches;
	}
	/**
	 * @return FileLine[]|Collection
	 */
	public function getFileLines(): Collection{
		if($fileLines = $this->lines){
			return collect($fileLines);
		}
		try {
			$c = $this->getOrReadContents();
			$this->setFileLines(explode("\n", $c));
		} catch (QMFileNotFoundException $e) {
			$this->logError("$this file not found!");
			$this->lines = [];
		}
		return collect($this->lines);
	}
	/**
	 * @param FileLine[]|string[]|string|null $array
	 */
	public function setFileLines($array): void{
		if(is_string($array)){
			$array = QMStr::explodeNewLines($array);
		}
		if(!$array){
			$this->lines = null;
			return;
		}
		$array = FileLine::instantiateArray($array, $this);
		if(empty($array)){
			le("!array");
		}
		$this->lines = $array;
	}
	/**
	 * @param string $search
	 * @param string $replace
	 * @return bool
	 */
	public function replace(string $search, string $replace): bool{
		$before = $this->getContents();
		$after = str_replace($search, $replace, $before);
		if($before === $after){
			QMLog::debug("No $search's in $this");
			return false;
		}
		$this->logInfo("Replaced $search with $replace");
		$this->writeContents($after);
		$this->setFileLines(null);
		return $after;
	}
	/**
	 * @param $str
	 * @throws QMFileNotFoundException
	 */
	public function appendIfAbsent($str){
		if(!$this->contains($str)){
			$this->append($str);
		}
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function contains(string $needle): ?FileLine{
		$c = $this->getOrReadContents();
		if(strpos($c, $needle) === false){
			return null;
		}
		$lines = $this->getFileLines();
		foreach($lines as $line){
			if($line->contains($needle)){
				return $line;
			}
		}
		return null;
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function append($str){
		$lines = $this->getFileLines();
		$lines[] = $str;
		$this->setFileLines($lines);
		$this->save();
	}
	public function save(): string {
		$contents = $this->getContents();
		return $this->writeContents($contents);
	}
	/**
	 * @return string
	 */
	public function implodeLines(): string{
		$strings = [];
		foreach($this->lines as $line){
			$strings[] = (string)$line;
		}
		return implode("\n", $strings);
	}
	/**
	 * @param $str
	 * @throws QMFileNotFoundException
	 */
	public function prefixIfAbsent($str){
		if(!$this->contains($str)){
			$this->prefix($str);
		}
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function prefix(string $str){
		if(static::GLOBAL_CONTENT_PREFIX && $this->contains(static::GLOBAL_CONTENT_PREFIX)){
			$this->insertAfterFirstLineContaining($str, static::GLOBAL_CONTENT_PREFIX);
			return;
		}
		$lines = $this->getFileLines();
		$lines = array_merge([$str], $lines);
		$this->setFileLines($lines);
		$this->save();
	}
	/**
	 * @param string|array|FileLine $toInsert
	 * @param string|int $after
	 * @throws QMFileNotFoundException
	 */
	public function insertAfterFirstLineContaining($toInsert, $after){
		if(is_int($after)){
			$number = $after;
		} elseif(is_string($after)){
			$number = $this->getFirstLineNumberContaining($after);
			if($number === null){
				$this->throwLogicException("$after not present");
				throw new \LogicException();
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$this->insertAt($toInsert, $number);
	}
	/**
	 * @param string $after
	 * @return int|null Null if not present
	 */
	public function getFirstLineNumberContaining(string $after): ?int{
		$line = $this->getFirstLineContaining($after);
		if(!$line){
			return null;
		}
		return $line->number;
	}
	/**
	 * @param string $needle
	 * @return FileLine
	 */
	protected function getFirstLineContaining(string $needle): ?FileLine{
		$lines = $this->getLinesContaining($needle);
		return $lines->first();
	}
	/**
	 * @param string $needle
	 * @return FileLine[]|Collection
	 */
	public function getLinesContaining(string $needle): Collection{
		return $this->linesContaining($needle);
	}
	/**
	 * @param string $needle
	 * @return FileLine[]|Collection
	 */
	public function linesContaining(string $needle): Collection{
		$lines = $this->getFileLines();
		$matches = [];
		foreach($lines as $line){
			if($line->contains($needle)){
				$matches[] = $line;
			}
		}
		return collect($matches);
	}
	/**
	 * @param string|string[]|FileLine[] $toInsert
	 * @param int|null $number
	 * @throws QMFileNotFoundException
	 */
	public function insertAt($toInsert, int $number): void{
		$lines = $this->getFileLines();
		if(!is_array($toInsert)){
			$toInsert = [$toInsert];
		}
		$toInsert = FileLine::instantiateArray($toInsert, $this);
		$lines->splice($number + 1, 0, $toInsert);
		$this->setFileLines($lines);
		$this->save();
	}
	/**
	 * @param string $toInsert
	 * @param string $after
	 * @throws QMFileNotFoundException
	 */
	public function insertAfterIfAbsent(string $toInsert, string $after){
		if(!$this->contains($toInsert)){
			$this->insertAfterFirstLineContaining($toInsert, $after);
		}
	}
	/**
	 * @return string
	 */
	public function __toString(): string {
		try {
			return $this->getPath();
		} catch (\Throwable $e) {
		    return __METHOD__.": could not get path because ".$e->getMessage();
		}
	}
	/**
	 * @param string $needle
	 * @param string $toInsert
	 * @throws QMFileNotFoundException
	 */
	public function insertAfterAllContaining(string $needle, string $toInsert){
		$containing = $this->getLinesContaining($needle);
		$max = $containing->max('number');
		$this->insertAfterFirstLineContaining($toInsert, $max);
	}
	/**
	 * @return string
	 */
	public function getDotsPathToRoot(): string{
		return $this->getFolderModel()->getDotsPathToRoot();
	}
	/**
	 * @return DynamicFolder
	 */
	public function getFolderModel(): DynamicFolder{
		return new DynamicFolder($this->getFolderPath());
	}
	/**
	 * @return string
	 */
	public function getRelativePath(): string{
		$real = $this->getRealPath();
		return relative_path($real);
	}
	public function getPathPrefix(): string{
		$a = $this->getDiskAdapter();
		return $a->getPathPrefix();
	}

    /**
     * @param string $contents
     * @return string
     */
	public function writeContents(string $contents): string
    {
		$disk = static::disk();
		$path = $this->getRelativeFilePath();
		if(AppMode::isWindows()){
			$contents = str_replace("\r\n", "\n", $contents);
		}
		$disk->put($path, $contents);
		//FileHelper::writeByFilePath($this, $contents);
        if(!AppMode::isWindows()){
            try {
                $this->setPermissions();
            } catch (\Throwable $e) {
                ConsoleLog::warning("Could not set permissions on $this because ".$e->getMessage());
            }
        } else {
	        FileHelper::dos2Unix($path);
        }
        return abs_path($path);
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function replaceLineContaining(string $needle, string $newLine){
		$line = $this->getFirstLineContaining($needle);
		if(!$line){
			throw new QMFileNotFoundException("File does not contain needle $needle to replace with $newLine");
		}
		$line->setString($newLine);
		$this->save();
	}
	/**
	 * @param string $existingLine
	 * @return int|null
	 */
	public function deleteLineContaining(string $existingLine): ?int{
		$number = $this->getFirstLineNumberContaining($existingLine);
		if($number){
			$this->logInfo("Deleting line $number containing $existingLine");
			$this->deleteLineNumber($number);
		}
		return $number;
	}
	/**
	 * @param string $message
	 * @param array $meta
	 */
	public function logInfo(string $message, $meta = []){
		QMLog::info("$this: $message");
	}
	public function deleteLineNumber(int $number){
		$lines = $this->getFileLines();
		unset($lines[$number]);
		$this->setFileLines($lines);
		$this->save();
	}
	/**
	 * @param int $number
	 * @param string $needle
	 * @return bool
	 * @throws QMFileNotFoundException
	 */
	public function lineContains(int $number, string $needle): bool{
		$line = $this->getLineByNumber($number);
		return $line->contains($needle);
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function getLineByNumber(int $number): FileLine{
		return $this->getFileLines()[$number];
	}
	/**
	 * @return string
	 * Only refers to content modification, and ignores meta data changes
	 */
	public function getModifiedAt(): string{
		return db_date($this->getMTime());
	}
	/**
	 * @return string
	 * The most recent content modification OR meta data changes
	 */
	public function getContentsOrMetaDataModifiedAt(): string{
		return db_date($this->getCTime());
	}
	/**
	 * @return string
	 */
	public function getAccessedAt(): string{
		return db_date($this->getATime());
	}
	/**
	 * @return mixed|string
	 */
	public function getDecoded(){
		return QMStr::decodeIfJson($this->getContents());
	}
	/**
	 * @return string
	 */
	public function getContents(): string{
		if(!$this->lines){
			try {
				return $this->originalContent = FileHelper::getContents($this->getPath());
			} catch (QMFileNotFoundException $e) {
				le($e);
			}
		}
		return $this->implodeLines();
	}
	/**
	 * @param $string
	 */
	public function setContents($string){
		$this->setFileLines($string);
	}
	/**
	 * @return string
	 */
	public function getRelativeFolder(): string{
		return FileHelper::getRelativePath($this->getFolderPath());
	}
	/**
	 *
	 */
	public function saveIfNotExists(){
		if($this->exists()){
			return;
		}
		$this->save();
	}
	/**
	 * @return bool
	 */
	public function exists(): bool{ return FileHelper::fileExists($this->getPath()); }
	/**
	 * @return bool
	 */
	public function fileExists(): bool{
		return $this->exists();
	}
	/**
	 * @param string $reason
	 */
	public function delete(string $reason){
		FileHelper::deleteFile($this->getPath(), $reason);
	}
	/**
	 * @return string
	 */
	public function getTitleCasedFileName(): string{
		return QMStr::titleCaseSlow(static::stripExtension($this->getFileName()));
	}
	/**
	 * @return string
	 */
	public function getTitleCasedPath(): string{
		return QMStr::titleCaseSlow(str_replace("/", " ", static::stripExtension($this->getPath())));
	}
	/**
	 *
	 */
	public function assertExists(){
		if(!$this->exists()){
			le("$this does not exist!");
		}
	}
	/**
	 * @param int $startNumber
	 * @param int $endNumber
	 * @return Collection
	 * @throws QMFileNotFoundException
	 */
	public function getLinesBetweenNumbers(int $startNumber, int $endNumber): Collection{
		return $this->getFileLines()->filter(function($v) use ($startNumber, $endNumber){
			/** @var FileLine $v */
			return $v->getNumber() > $startNumber && $v->getNumber() < $endNumber;
		});
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function deleteLinesAfter($str){
		$lines = $this->getLinesAfter($str);
		foreach($lines as $line){
			$line->delete();
		}
	}
	/**
	 * @param $str
	 * @return FileLine[]|Collection
	 * @throws QMFileNotFoundException
	 */
	public function getLinesAfter($str){
		$lines = $this->getLinesContaining($str);
		$deleteAfter = $lines->first();
		$lines = $this->getFileLines();
		return $lines->filter(function($line) use ($deleteAfter){
			/** @var FileLine $line */
			return $line->getNumber() > $deleteAfter->getNumber();
		});
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function deleteBetweenAndIncludingStrings(string $start, string $end){
		$lines = $this->getLinesBetweenAndIncludingStrings($start, $end);
		foreach($lines as $line){
			$line->delete();
		}
	}
	/**
	 * @param string $start
	 * @param string $end
	 * @return FileLine[]
	 * @throws QMFileNotFoundException
	 */
	public function getLinesBetweenAndIncludingStrings(string $start, string $end): array{
		$all = $this->getFileLines();
		$matches = [];
		foreach($all as $line){
			if(!isset($startLine) && $line->contains($start)){
				$startLine = $line;
			}
			if(isset($startLine)){
				$matches[] = $line;
				if($line->contains($end)){
					break;
				}
			}
		}
		return $matches;
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function deleteBetweenStrings(string $start, string $end){
		$lines = $this->getLinesBetweenStrings($start, $end);
		foreach($lines as $line){
			$line->delete();
		}
	}
	/**
	 * @param string $start
	 * @param string $end
	 * @return FileLine[]
	 * @throws QMFileNotFoundException
	 */
	public function getLinesBetweenStrings(string $start, string $end): array{
		$all = $this->getFileLines();
		$matches = [];
		foreach($all as $line){
			if(!isset($startLine) && $line->contains($start)){
				$startLine = $line;
				continue;
			}
			if(isset($startLine)){
				if($line->contains($end)){
					break;
				}
				$matches[] = $line;
			}
		}
		return $matches;
	}
	/**
	 * @param string $newContent
	 */
	public function saveNewContents(string $newContent){
		$this->setFileLines([]);
		FileHelper::write($this->getPath(), $newContent);
	}
	/**
	 * @param string|null $message
	 * @return string
	 */
	public function logPhpStormUrl(string $message = null): string{
		PHPStormButton::log($this->getPath(), 0, $message ?? $this->getFileName());
		return $this->getPhpStormUrl();
	}
	/**
	 * @return string
	 */
	public function getPhpStormUrl(): string{
		return PHPStormButton::redirectUrl($this->getPath(), 0);
	}
	/**
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	public function getContentsBetween(string $start, string $end): string{
		$c = $this->getContents();
		return QMStr::between($c, $start, $end, $c);
	}
	/**
	 * @return string
	 */
	public function getOriginalContent(): string{
		return $this->originalContent;
	}
	/**
	 * @param string|null $originalContent
	 * @return UntypedFile
	 */
	public function setOriginalContent(?string $originalContent): self{
		$this->originalContent = $originalContent;
		return $this;
	}
	/**
	 * @param string|array $needles
	 */
	public function removeLinesContaining($needles){
		if(is_string($needles)){
			$needles = [$needles];
		}
		foreach($needles as $needle){
			try {
				FileHelper::removeLinesContaining($this->getPath(), $needle);
			} catch (QMFileNotFoundException $e) {
				le($e);
				throw new \LogicException();
			}
		}
		$this->setOriginalContent(null);
		$this->setFileLines(null);
		try {
			$this->getFileLines();
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return QMStr::pathToTitle($this->name(true));
	}
	/**
	 * @param bool $stripExtension
	 * @return string
	 */
	public function name(bool $stripExtension): string{
		return FileHelper::pathToName($this->getPath(), $stripExtension);
	}
	/**
	 * @param string|SplFileInfo $file
	 * @return UntypedFile
	 */
	public function setAbsPath($file): self{
		if($file instanceof SplFileInfo){
			$this->fileInfo = $file;
			$this->setPath($file->getRealPath());
		} elseif($file){
			$this->setPath(abs_path($file));
		} else{
			le("Please provide file param");
		}
		return $this;
	}
	/**
	 * @param string $path
	 * @param        $content
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function create(string $path, $content){
		$path = FileHelper::write($path, $content);
		return static::find($path);
	}
	/**
	 * @param string $fileModelOrClass
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function find(string $fileModelOrClass){
		$path = abs_path($fileModelOrClass);
		return new static($path);
	}
	/**
	 * @return string
	 */
	public function getBasenameWithoutSuffix(): string{
		return pathinfo($this->getFilename())['filename'];
	}
	/**
	 * @return string
	 */
	public function getRealPathWithoutSuffix(): string{
		return Strings::replace($this->getRealPath(), self::LAST_SUFFIX_REGEX, '');
	}
	/**
	 * @return string
	 */
	public function getRelativeFilePath(): string{
		return $this->getRelativePath();
	}
	/**
	 * @return string
	 */
	public function getRelativeDirectoryPath(): string{
		return $this->getRelativePath();
	}
	/**
	 * @throws DirectoryNotFoundException
	 */
	public function getRelativeFilePathFromDirectory(string $directory): string{
		if(!file_exists($directory)){
			throw new DirectoryNotFoundException(sprintf('Directory "%s" was not found in %s.', $directory,
				self::class));
		}
		$relativeFilePath =
			$this->smartFileSystem->makePathRelative($this->getNormalizedRealPath(), (string)realpath($directory));
		return rtrim($relativeFilePath, DIRECTORY_SEPARATOR);
	}
	/**
	 * @throws DirectoryNotFoundException
	 */
	public function getRelativeFilePathFromCwdInTests(): string{
		return $this->getRelativeFilePathFromDirectory(getcwd());
	}
	/**
	 * @throws DirectoryNotFoundException
	 */
	public function getRelativeFilePathFromCwd(): string{
		return $this->getRelativeFilePathFromDirectory(getcwd());
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public function endsWith(string $string): bool{
		return \str_ends_with($this->getNormalizedRealPath(), $string);
	}
	/**
	 * @return string
	 */
	public function getRealPath(): string{
		// for phar compatibility @see https://github.com/rectorphp/rector/commit/e5d7cee69558f7e6b35d995a5ca03fa481b0407c
		return parent::getRealPath() ?: $this->getPathname();
	}
	/**
	 * @return string
	 */
	public function getRealPathDirectory(): string{
		return dirname($this->getRealPath());
	}
	/**
	 * @param string $partialPath
	 * @return bool
	 */
	public function startsWith(string $partialPath): bool{
		return \str_starts_with($this->getNormalizedRealPath(), $partialPath);
	}
	/**
	 * @return string
	 */
	private function getNormalizedRealPath(): string{
		return $this->normalizePath($this->getRealPath());
	}
	/**
	 * @param string $path
	 * @return string
	 */
	private function normalizePath(string $path): string{
		return str_replace('\\', DIRECTORY_SEPARATOR, $path);
	}
	/**
	 * @return string
	 */
	public function getFilenameWithoutExtension(): string{
		$filename = $this->getFilename();
		return pathinfo($filename, \PATHINFO_FILENAME);
	}

    /**
     * @param string|null $owner
     * @param string|null $perms
     * @param string|null $group
     * @return void
     * @throws FilePermissionsException
     */
    public function setPermissions(string $owner = null, string $perms = null, string $group = null){
		if(!$owner){
			$owner = $this->getDefinedOwner();
		}
		if(!$perms){
			$perms = $this->getDefinedPermissions();
		}
		if(!$group){
			$group = $this->getDefinedGroup();
		}
		$currentOwner = $this->getOwnerName();
		if($currentOwner !== $owner){
			ThisComputer::ownFile($this->getRealPath(), $owner, $group);
		}
		$currentPerms = $this->getPermsString();
		if($currentPerms !== $perms){
			$this->logInfo("Changing permissions from $currentPerms to $perms...");
            try {
                ThisComputer::chmod($this->getRealPath(), $perms);
            } catch (\Throwable $e) {
                ConsoleLog::warning("Could not set permissions for file {$this->getRealPath()} because of {$e->getMessage()}");
            }
		}
	}
	/**
	 * @return string
	 */
	public function getPermsString(): string{
		return substr(sprintf('%o', parent::getPerms()), -4);
	}
	public function getOwnerName(): ?string{
		if($this->owner){return $this->owner;}
		$userInfo = posix_getpwuid(parent::getOwner());
		return $this->owner = $userInfo['name'];
	}
	public function getDefinedOwner(): string{ return ThisComputer::user(); }
	public function getDefinedGroup(): string{ return ThisComputer::user(); }
	public function getDefinedPermissions(): string{
		return UntypedFile::$permissions['file']['public'];
	}
	/**
	 * @return Local|FilesystemAdapter
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function disk(): FilesystemAdapter{
		$res = Storage::disk(static::getDiskName());
		return $res;
	}
	/**
	 * @return Local
	 */
	public function getDiskAdapter(): AdapterInterface{
		$disk = static::disk();
		return $disk->getDriver()->getAdapter();
	}
	/**
	 * @return array|false|void
	 * @throws FileNotFoundException
	 */
	public function getMetadata(){
		return static::disk()->getMetadata($this->getRelativePath());
	}
	/**
	 * @return string
	 * @throws FileNotFoundException
	 */
	public function getMimetype(): string{
		return static::disk()->getMimetype($this->getRelativePath());
	}
	/**
	 * @return int
	 * @throws FileNotFoundException
	 */
	public function getTimestamp(): int{
		return static::disk()->getTimestamp($this->getRelativePath());
	}
	/**
	 * @return array|false|string
	 */
	public function getVisibility(){
		return static::disk()->getVisibility($this->getRelativePath());
	}
	/**
	 * @param $visibility
	 * @return array|bool
	 */
	public function setVisibility($visibility){
		return static::disk()->setVisibility($this->getRelativePath(), $visibility);
	}
	protected function ownFile(){
		ThisComputer::ownFile($this->getRealPath(), ThisComputer::user());
	}
	/**
	 * @param string $string
	 * @return static|null
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function findContaining(string $string): ?UntypedFile{
		$all = static::all();
		foreach($all as $one){
			/** @var static $one */
			if($one->contains($string)){
				return $one;
			}
		}
		return null;
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function readContents(): string{
		$c = FileHelper::getContents($this->getPath());
		$this->setOriginalContent($c);
		return $c;
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function getOrReadContents(): string{
		if($this->originalContent){
			return $this->originalContent;
		}
		return $this->readContents();
	}
	public function getLogMetaData(?array $meta = []): array{
		if($this->absPath){$meta['absPath'] = $this->absPath;}
		// Don't call parent::getLogMetaData because getName() requires param
		return $meta;
	}

    /**
     * @return string
     * @throws FilePermissionsException
     */
	public function getGroupName(): string {
		if(!function_exists('posix_getgrgid')){
			return $this->getGroup();
		}
		$arr = posix_getgrgid($this->getGroup());
        if(!$arr){
            throw new FilePermissionsException("Could not get group name for file {$this->getRealPath()}");
        }
		return $arr['name'];
	}
}
