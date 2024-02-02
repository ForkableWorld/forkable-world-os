<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\CodeGenerators\ClassTypePrinter;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\FileLine;
use App\Folders\DynamicFolder;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use App\Traits\FileTraits\IsFormattableFile;
use App\Traits\HasPropertyModels;
use App\Types\JsonHelper;
use App\Types\QMConstantModel;
use App\Types\QMStr;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Krlove\CodeGenerator\Model\ConstantModel;
use Krlove\CodeGenerator\Model\MethodModel;
use Krlove\CodeGenerator\Model\Traits\DocBlockTrait;
use Mockery\Generator\DefinedTargetClass;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Constant;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\EnumCase;
use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PromotedParameter;
use Nette\PhpGenerator\Property;
use Nette\PhpGenerator\Traits\AttributeAware;
use Nette\PhpGenerator\Traits\CommentAware;
use Nette\PhpGenerator\TraitUse;
use Nette\PhpGenerator\Type;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionMethod;

/**
 * @package App\Files\PHP
 * @mixin ClassType
 */
class PhpClassFile extends AbstractPhpFile {
	use HasPropertyModels, ForwardsCalls;
	const SCOPE_PRIVATE = "private";
	const SCOPE_PROTECTED = "protected";
	const SCOPE_PUBLIC = "public";
	public string $className;
	use DocBlockTrait;
	use IsFormattableFile;
	protected array $constants = [];
	protected PhpNamespace $phpNamespace;
	protected ClassType $classType;
    use \Nette\SmartObject;
    use CommentAware;
    use AttributeAware;

    /** @var int */
    public $wrapLength = 120;

    /** @var string */
    protected $indentation = "\t";

    /** @var int */
    protected $linesBetweenProperties = 0;

    /** @var int */
    protected $linesBetweenMethods = 2;

    /** @var string */
    protected $returnTypeColon = ': ';

    /** @var ?PhpNamespace */
    protected $namespace;

    /** @var ?Dumper */
    protected $dumper;

    /** @var bool */
    private $resolveTypes = true;





    public const
        TYPE_CLASS = 'class',
        TYPE_INTERFACE = 'interface',
        TYPE_TRAIT = 'trait',
        TYPE_ENUM = 'enum';

    public const
        VisibilityPublic = 'public',
        VisibilityProtected = 'protected',
        VisibilityPrivate = 'private';

    public const
        VISIBILITY_PUBLIC = self::VisibilityPublic,
        VISIBILITY_PROTECTED = self::VisibilityProtected,
        VISIBILITY_PRIVATE = self::VisibilityPrivate;

    /** @var string|null */
    protected $name;

    /** @var string  class|interface|trait */
    protected $type = self::TYPE_CLASS;

    /** @var bool */
    protected $final = false;

    /** @var bool */
    protected $abstract = false;

    /** @var string|string[] */
    protected $extends = [];

    /** @var string[] */
    protected $implements = [];

    /** @var TraitUse[] */
    protected $traits = [];

    /** @var Constant[] name => Constant */
    protected $consts = [];

    /** @var Property[] name => Property */
    protected $properties = [];

    /** @var \Nette\PhpGenerator\Method[] name => Method */
    protected $methods = [];

    /** @var EnumCase[] name => EnumCase */
    protected $cases = [];
	/**
	 * @param string|object $fileModelOrClass
	 */
	public function __construct($fileModelOrClass){
		parent::__construct(FileHelper::getPathToModelOrClass($fileModelOrClass));
        $class = $this->getShortClass();
        $this->setName($class);
        $this->namespace = QMStr::classToNameSpace($this->getNameSpace());
        $this->dumper = new Dumper;
	}
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::FOLDER_APP_MODELS;
	}
	/**
	 * @return array
	 */
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::FOLDER_APP,
			DynamicFolder::FOLDER_APP_PHPUNIT_JOBS,
		];
	}
	/**
	 * @param string $needle
	 * @param string|null $folder
	 */
	public static function deleteLinesContaining(string $needle, string $folder = null){
		if($folder){
			$lines = static::getProjectLinesContaining($needle);
		} else{
			$lines = static::getFilesAndLinesContaining($needle, $folder);
		}
		foreach($lines as $line){
			$line->delete();
		}
	}
	/**
	 * @param string $oldClass
	 * @param string $newClass
	 */
	public static function renameClass(string $oldClass, string $newClass){
		$file = PhpClassFile::find($oldClass);
		$file->rename($newClass);
	}
	/**
	 * @param string $newClass
	 */
	public function rename(string $newClass){
		$oldClass = $this->getClassName();
		$newPath = FileHelper::classToPath($newClass);
		$oldNS = $this->getNameSpace();
		$newNS = QMStr::pathToNameSpace($newPath);
		$this->replace($oldNS, $newNS);
		FileHelper::rename($this->absPath, $newPath);
		$files = static::getProjectFilesContaining($oldClass);
		foreach($files as $file){
			$file->replace($oldClass, $newClass);
			$file->replace(QMStr::toShortClassName($oldClass), QMStr::toShortClassName($newClass));
		}
	}
	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->absPath;
	}
	/**
	 * @param array $files
	 * @param string $newNamespace
	 * @throws QMFileNotFoundException
	 */
	public static function moveClasses(array $files, string $newNamespace): void{
		foreach($files as $file){
			$contents = FileHelper::getContents($file);
			$oldNameSpace = "App\\Traits";
			$newContents = str_replace("namespace $oldNameSpace;", "namespace $newNamespace;", $contents);
			$oldClass = FileHelper::pathToClass($file);
			$newClass = "$newNamespace\\" . QMStr::toShortClassName($oldClass);
			$newPath = FileHelper::classToPath($newClass);
			FileHelper::writeByFilePath($newPath, $newContents);
			FileHelper::deleteFile($file, __METHOD__);
			FileHelper::replaceClassInFiles($oldClass, $newClass);
		}
	}
	/**
	 * @param string $getFullClass
	 * @return string
	 */
	public static function classToNameSpace(string $getFullClass): string{
		return FileHelper::classToNamespace($getFullClass);
	}
	/**
	 * @param string $fullClass
	 * @return string
	 */
	public static function toFilePath(string $fullClass): string{
		return FileHelper::classToPath($fullClass);
	}
	/**
	 * @param string $search
	 * @param string $replace
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public static function replaceInClassNames(string $search, string $replace){
		$classes = self::getClassesLike($search);
		foreach($classes as $path => $file){
			$newClass = str_replace($search, $replace, $file->getClassName());
			$file->rename($newClass);
		}
		foreach($classes as $path => $file){
			$newClass = str_replace($search, $replace, $file->getClassName());
			FileHelper::renameFoldersLike($file->getShortClass(), QMStr::toShortClassName($newClass));
		}
	}
	/**
	 * @param string $needle
	 * @return static[]
	 */
	public static function getClassesLike(string $needle): array{
		$files = static::getProjectFileNamesContaining($needle);
		$classes = [];
		foreach($files as $file){
			$classes[$file->getRealPath()] = new static($file);
		}
		return $classes;
	}
	/**
	 * @return string
	 */
	public function getShortClass(): string{
		return QMStr::toShortClassName($this->getClassName());
	}
	/**
	 * @return array
	 */
	public static function getClasses(): array{
		$files = static::get();
		$classes = [];
		foreach($files as $file){
			$classes[] = $file->getClassName();
		}
		return $classes;
	}
	/**
	 * @param string $folder
	 * @param string $trait
	 */
	public static function addTraitToFiles(string $folder, string $trait){
		$classTypes = static::getTraitsAndClassModelsInFolder($folder);
		foreach($classTypes as $classType){
			$classType->addTrait($trait);
			$classType->save();
		}
	}
	/**
	 * @param string $fileModelOrClass
	 * @return string
	 */
	public static function generatePathToModelOrClass(string $fileModelOrClass): string{
		$path = FileHelper::getPathToModelOrClass($fileModelOrClass);
		return $path;
	}
	/**
	 * @param string $folder
	 * @param false $recursive
	 * @param string|null $notLike
	 * @return static[]
	 */
	public static function getTraitsAndClassModelsInFolder(string $folder, bool $recursive = false,
		string $notLike = null): array{
		return AbstractPhpFile::getTraitsAndClassesInFolder($folder, $recursive, $notLike);
	}
	public function getContents(): string{
		try {
			$printer = new ClassTypePrinter($this);
			$code = $printer->printFile($this->getPhpFile());
			$code = QMStr::formatPHP($code);
		} catch (\Throwable $e) {
			le($e, $this);
		}
		return $code;
	}
	/**
	 * @return PhpFile
	 */
	protected function getPhpFile(): PhpFile{
		$file = new PhpFile;
		$ns = $this->getPhpNamespace();
		$file->addNamespace($ns);
		return $file;
	}
	/**
	 * @return PhpNamespace
	 */
	protected function getPhpNamespace(): PhpNamespace{
		if(isset($this->phpNamespace)){
			return $this->phpNamespace;
		}
		$ns = $this->phpNamespace = new PhpNamespace($this->getNameSpace());
		$this->loadUseStatements();
		$ct = $this->getClassType();
		$ns->add($ct);
		return $this->phpNamespace;
	}
	/**
	 *
	 */
	protected function loadUseStatements(): void{
		$lines = $this->getLinesStartingWith("use ", "class ");
		foreach($lines as $i => $line){
			$usedClass = trim($line->getBetween("use ", ";"));
			$arr = explode(" ", $usedClass);
			$this->addUse($arr[0], $arr[1] ?? QMStr::toShortClassName($arr[0]));
		}
	}
	/**
	 * @param string $class
	 * @param string|null $alias
	 */
	public function addUse(string $class, string $alias = null): void{
		$ns = $this->getPhpNamespace();
		$ns->addUse($class, $alias);
	}
	/**
	 * @return ClassType
	 */
	public function getClassType(): ClassType{
		if(isset($this->classType)){
			return $this->classType;
		}
		$this->classType = ClassType::withBodiesFrom($this->getClassName());
		return $this->classType;
	}
	/**
	 * @return string
	 */
	public function getClassName(): string{
		if(isset($this->className)){
			return $this->className;
		}
		return $this->className = FileHelper::pathToClass($this->absPath);
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isStaticReference($value): bool{
		return strpos($value, "::") !== false;
	}

    /**
     * @throws QMFileNotFoundException
     */
    public static function addTraitToFilesContaining(string $needle, string $traitClass, string $folder){
		$files = FileFinder::getFilesContaining($folder, $needle, true);
		foreach($files as $file){
			FileHelper::addTrait($traitClass, $file);
		}
	}
	public static function getClassesInFolder(string $getAbsolutePath): array{
		return FileHelper::getClassesInFolder($getAbsolutePath);
	}
	/**
	 * @param string $getAbsolutePath
	 * @param bool $recursive
	 * @param array $excludeClasses
	 * @return object[]
	 */
	public static function instantiateModelsInFolder(string $getAbsolutePath, bool $recursive = true,
		array $excludeClasses = []): array{
		return FileHelper::instantiateModelsInFolder($getAbsolutePath, $recursive, $excludeClasses);
	}
	/**
	 *
	 */
	public function cleanup(){
		$contents = $this->getContents();
		$contents = str_replace("
{
    ", "{
    ", $contents);
		$this->writeContents($contents);
	}
	/**
	 * @param string $name
	 * @return bool|false
	 */
	public function hasExistingMethod(string $name): bool{
		return strpos($this->getExistingContents(), " function $name(") !== false;
	}
	/**
	 * @param string $name
	 * @return bool|false
	 * @throws QMFileNotFoundException
	 */
	public function methodInheritedFromTrait(string $name): bool{
		if($this->hasExistingMethod($name)){
			return false;
		}
		$traits = $this->getTraits();
		foreach($traits as $trait){
			$path = FileHelper::classToPath($trait);
			$contains = FileHelper::getLineContainingString($path, " function $name(");
			if($contains){
				return true;
			}
		}
		return false;
	}
	/**
	 *
	 */
	public function reformat(): void{
		$this->trimRightSideOfLines();
		$this->removeNewLinesInComments();
	}
	protected function removeNewLinesInComments(){
		$this->replace("*
 *", "*");
	}
	/**
	 * @param string $class
	 * @param string $name
	 */
	public function replaceStringsWithConstantReference(string $class, string $name){
		$value = constant("$class::$name");
		$replace = QMStr::toShortClassName($class) . "::" . $name;
		$changedA = $this->replace("'$value'", $replace);
		$changedB = $this->replace('"' . $value . '"', $replace);
		if($changedA || $changedB){
			$this->addUse(static::class);
		} else{
			$this->logInfo("No usages of $value found in $this...");
		}
	}

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
	 * @return int|null
	 */
	protected function getBodyStartingLine(): int{
		$first = $this->getFirstLineNumberContaining("class ");
		if(!$first){
			$first = $this->getFirstLineNumberContaining("trait ");
		}
		$bodyStartsAt = $first + 1;
		if($this->lineContains($bodyStartsAt, "{")){
			$bodyStartsAt++;
		}
		return $bodyStartsAt;
	}
	/**
	 * @param array $arr
	 */
	public function addConstants(array $arr){
		foreach($arr as $name => $value){
			$this->addConstant($name, $value);
		}
	}
	public function makeAbstract(){
		if(!$this->contains("abstract class ")){
			$this->replace("class ", "abstract class");
		}
	}
	/**
	 * @param string $name
	 * @return FileLine|null
	 */
	public function hasConstant(string $name): ?ConstantModel{
		$constants = $this->getConstants();
		$const = BaseNameProperty::findInArray($name, $constants);
		return $const;
	}
	/**
	 * @return QMConstantModel[]
	 */
	public function getConstants(): array{
		return $this->constants;
	}
	public function generateUnitTest(): string
    {
		$test = $this->getUnitTestFile();
		return $test->generate();
	}
	public function addGettersAndSetters(): void{
		$props = $this->getPropertyModels();
		foreach($props as $property){
			$name = $property->name;
			if(!$this->hasGetter($name)){
				$this->addGetter($name);
			}
			if(!$this->hasSetter($name)){
				$this->addSetter($name);
			}
		}
		$this->save();
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	protected function hasGetter(string $name){
		$code = $this->getContents();
		return stripos($code, "function " . $this->getGetterFunctionName($name));
	}
	/**
	 * @param string $attribute
	 * @return string
	 */
	protected function getGetterFunctionName(string $attribute): string{
		return "get" . ucfirst(QMStr::camelize($attribute));
	}
	/**
	 * @param string $attribute
	 */
	protected function addGetter(string $attribute){
		$method = new MethodModel($this->getGetterFunctionName($attribute));
		$this->addMethod($method);
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	protected function hasSetter(string $name): bool{
		$code = $this->getContents();
		return stripos($code, "function " . $this->getSetterFunctionName($name));
	}
	/**
	 * @param string $attribute
	 * @return string
	 */
	protected function getSetterFunctionName(string $attribute): string{
		return "set" . ucfirst(QMStr::camelize($attribute));
	}
	/**
	 * @param string $attribute
	 */
	protected function addSetter(string $attribute){
		$method = new MethodModel($this->getSetterFunctionName($attribute));
		$this->addMethod($method);
	}
	/**
	 * @return Collection|QMMethodModel[]
	 */
	public function getNonInheritedMethods(): Collection{
		return collect($this->getMethods())->filter(function($m){
			/** @var QMMethodModel $m */
			return !$m->isInherited();
		});
	}
	/**
	 * @return Method[]|ReflectionMethod[]
	 */
	protected function getReflectionMethods(): array{
		$dtc = $this->getDefinedTargetClass();
		return $dtc->getMethods();
	}
	/**
	 * @return DefinedTargetClass
	 */
	public function getDefinedTargetClass(): DefinedTargetClass{
		return DefinedTargetClass::factory($this->getClassName());
	}
	/**
	 * @return string|null
	 */
	public function getParent(): ?string{
		$parents = $this->getParents();
		return $parents[0] ?? null;
	}
	/**
	 * @return string[]
	 */
	public function getParents(): array{
		$class = $this->getReflectionClass();
		$parents = [];
		while($parent = $class->getParentClass()){
			$parents[] = $parent->getName();
			$class = $parent;
		}
		return $parents;
	}
	/**
	 * @return ReflectionClass
	 */
	protected function getReflectionClass(): ReflectionClass{
		return new ReflectionClass($this->getClassName());
	}
	/**
	 * Dynamically retrieve attributes on the model.
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key){
		return $this->getClassType()->$key;
	}
	/**
	 * Dynamically set attributes on the model.
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $key, $value){
		$this->getClassType()->$key = $value;
	}
	/**
	 * Handle dynamic method calls into the model.
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters){
		$l = $this->getClassType();
		return $this->forwardCallTo($l, $method, $parameters);
	}
	/**
	 * @param $staticReference
	 * @return string
	 */
	protected function addImportAndGetAbbreviatedStaticReference($staticReference): string{
		$class = self::getClassFromStaticReference($staticReference);
		$abbreviatedStaticReference = self::abbreviateStaticReference($staticReference);
		FileHelper::addUseImportStatement($class, $this);
		return $abbreviatedStaticReference;
	}
	/**
	 * @param $value
	 * @return string|null
	 */
	protected static function getClassFromStaticReference($value): ?string{
		$class = QMStr::before("::", $value);
		return $class;
	}
	/**
	 * @param $value
	 * @return string|null
	 */
	protected static function abbreviateStaticReference($value): ?string{
		$value = QMStr::afterLast($value, "\\");
		return $value;
	}
	/**
	 * @param string $name
	 * @param string $constantDeclarationLine
	 */
	protected function replaceConstant(string $name, string $constantDeclarationLine): void{
		$this->replaceLineContaining("const " . $name, $constantDeclarationLine);
	}
	/**
	 * @param string $newFolder
	 */
	protected function replaceReferences(string $newFolder): void{
		parent::replaceReferences($newFolder);
		$oldClass = $this->getClassName();
		$newPath = $newFolder . "/" . $this->getFileName();
		$newFile = static::find($newPath);
		static::replaceInAll($oldClass, $newFile->getClassName());
	}
	public function getNameSpace(): string{
        if(isset($this->namespace)){
            return $this->namespace;
        }
        $class = $this->getClassName();
        return QMStr::classToNameSpace($class);
	}
	/**
	 * @param string $fileModelOrClass
	 * @param $content
	 * @return static
	 */
	public static function createPrettyJsonFile(string $fileModelOrClass, $content): self{
		$json = JsonHelper::prettyJsonEncode($content);
		$path = static::generatePathToModelOrClass($fileModelOrClass);
		$path = JsonHelper::appendJsonExtensionIfNecessary($path);
		return static::create($path, $json);
	}
	/**
	 * @param string $fileModelOrClass
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function find(string $fileModelOrClass){
		$path = static::generatePathToModelOrClass($fileModelOrClass);
		return new static($path);
	}
	/**
	 * @param string $path
	 * @param        $content
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function create(string $path, $content){
		$path = static::generatePathToModelOrClass($path);
		return parent::create($path, $content);
	}
	/**
	 * @param string $str
	 */
	protected function appendCode(string $str): void{
		$code = QMStr::appendCode($this->getContents(), $str);
		$this->setContents($code);
	}

    /**
     * @throws QMFileNotFoundException
     */
    private function getExistingContents(): ?string{
		return FileHelper::getContents($this->getRealPath());
	}

    /**
     * @param string|null $className
     * @return UnitTestFile
     */
    public function getUnitTestFile(string $className = null): UnitTestFile
    {
        try {
            return new UnitTestFile($className ?? $this->getClassName());
        } catch (InvalidFilePathException $e) {
            le($e);
        }
    }

    /**
     * @return array
     */
    private function getExistingMethods(): array
    {
        $methods = $this->getReflectionMethods();
        foreach ($methods as $method) {
            if ($method->isInternal()) $this->methods[] = new QMMethodModel($method, $this);
        }
        return $this->methods;
    }
    public static function class(?string $name): self
    {
        return new self($name);
    }


    public static function interface(string $name): self
    {
        return (new self($name))->setType(self::TYPE_INTERFACE);
    }


    public static function trait(string $name): self
    {
        return (new self($name))->setType(self::TYPE_TRAIT);
    }


    public static function enum(string $name): self
    {
        return (new self($name))->setType(self::TYPE_ENUM);
    }


    /**
     * @param  string|object  $class
     */
    public static function from($class, bool $withBodies = false, bool $materializeTraits = true)
    {
        return (new Factory)
            ->fromClassReflection(new \ReflectionClass($class), $withBodies, $materializeTraits);
    }


    /**
     * @param  string|object  $class
     */
    public static function withBodiesFrom($class)
    {
        return (new Factory)
            ->fromClassReflection(new \ReflectionClass($class), true);
    }


    public static function fromCode(string $code)
    {
        return (new Factory)
            ->fromClassCode($code);
    }



    public function __toString(): string
    {
        try {
            $this->printClass($this->getPhpNamespace());
        } catch (\Throwable $e) {
            if (PHP_VERSION_ID >= 70400) {
                throw $e;
            }

            trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);

        }
        return '';
    }


    /** @return static */
    public function setName(?string $name): self
    {
        if ($name !== null && (!Helpers::isIdentifier($name) || isset(Helpers::Keywords[strtolower($name)]))) {
            throw new \Nette\InvalidArgumentException("Value '$name' is not valid class name.");
        }

        $this->name = $name;
        return $this;
    }


    /** @deprecated */
    public function setClass(): self
    {
        $this->type = self::TYPE_CLASS;
        return $this;
    }


    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }


    /** @return static */
    public function setInterface(): self
    {
        $this->type = self::TYPE_INTERFACE;
        return $this;
    }


    public function isInterface(): bool
    {
        return $this->type === self::TYPE_INTERFACE;
    }


    /** @return static */
    public function setTrait(): self
    {
        $this->type = self::TYPE_TRAIT;
        return $this;
    }


    public function isTrait(): bool
    {
        return $this->type === self::TYPE_TRAIT;
    }


    public function isEnum(): bool
    {
        return $this->type === self::TYPE_ENUM;
    }


    /** @return static */
    public function setType(string $type): self
    {
        if (!in_array($type, [self::TYPE_CLASS, self::TYPE_INTERFACE, self::TYPE_TRAIT, self::TYPE_ENUM], true)) {
            throw new \Nette\InvalidArgumentException('Argument must be class|interface|trait|enum.');
        }

        $this->type = $type;
        return $this;
    }


    public function getType(): string
    {
        return $this->type;
    }


    /** @return static */
    public function setFinal(bool $state = true): self
    {
        $this->final = $state;
        return $this;
    }


    public function isFinal(): bool
    {
        return $this->final;
    }


    /** @return static */
    public function setAbstract(bool $state = true): self
    {
        $this->abstract = $state;
        return $this;
    }


    public function isAbstract(): bool
    {
        return $this->abstract;
    }


    /**
     * @param  string|string[]  $names
     * @return static
     */
    public function setExtends($names): self
    {
        if (!is_string($names) && !is_array($names)) {
            throw new \Nette\InvalidArgumentException('Argument must be string or string[].');
        }

        $this->validateNames((array) $names);
        $this->extends = $names;
        return $this;
    }


    /** @return string|string[] */
    public function getExtends()
    {
        return $this->extends;
    }


    /** @return static */
    public function addExtend(string $name): self
    {
        $this->validateNames([$name]);
        $this->extends = (array) $this->extends;
        $this->extends[] = $name;
        return $this;
    }


    /**
     * @param  string[]  $names
     * @return static
     */
    public function setImplements(array $names): self
    {
        $this->validateNames($names);
        $this->implements = $names;
        return $this;
    }


    /** @return string[] */
    public function getImplements(): array
    {
        return $this->implements;
    }


    /** @return static */
    public function addImplement(string $name): self
    {
        $this->validateNames([$name]);
        $this->implements[] = $name;
        return $this;
    }


    /** @return static */
    public function removeImplement(string $name): self
    {
        $this->implements = array_diff($this->implements, [$name]);
        return $this;
    }


    /**
     * @param  string[]|TraitUse[]  $traits
     * @return static
     */
    public function setTraits(array $traits): self
    {
        $this->traits = [];
        foreach ($traits as $trait) {
            if (!$trait instanceof TraitUse) {
                $trait = new TraitUse($trait);
            }

            $this->traits[$trait->getName()] = $trait;
        }

        return $this;
    }


    /** @return string[] */
    public function getTraits(): array
    {
        return array_keys($this->traits);
    }


    /** @internal */
    public function getTraitResolutions(): array
    {
        return $this->traits;
    }


    /**
     * @param  array|bool  $resolutions
     * @return static|TraitUse
     */
    public function addTrait(string $name, $resolutions = [])
    {
        $this->traits[$name] = $trait = new TraitUse($name);
        if ($resolutions === true) {
            return $trait;
        }

        array_map(function ($item) use ($trait) {
            $trait->addResolution($item);
        }, $resolutions);
        return $this;
    }


    /** @return static */
    public function removeTrait(string $name): self
    {
        unset($this->traits[$name]);
        return $this;
    }


    /**
     * @param \Nette\PhpGenerator\Method|Property|Constant|EnumCase|TraitUse  $member
     * @return static
     */
    public function addMember($member): self
    {
        if ($member instanceof Method) {
            if ($this->isInterface()) {
                $member->setBody(null);
            }

            $this->methods[strtolower($member->getName())] = $member;

        } elseif ($member instanceof Property) {
            $this->properties[$member->getName()] = $member;

        } elseif ($member instanceof Constant) {
            $this->consts[$member->getName()] = $member;

        } elseif ($member instanceof EnumCase) {
            $this->cases[$member->getName()] = $member;

        } elseif ($member instanceof TraitUse) {
            $this->traits[$member->getName()] = $member;

        } else {
            throw new \Nette\InvalidArgumentException('Argument must be Method|Property|Constant|EnumCase|TraitUse.');
        }

        return $this;
    }


    /**
     * @param  Constant[]|mixed[]  $consts
     * @return static
     */
    public function setConstants(array $consts): self
    {
        $this->consts = [];
        foreach ($consts as $k => $const) {
            if (!$const instanceof Constant) {
                $const = (new Constant($k))->setValue($const)->setPublic();
            }

            $this->consts[$const->getName()] = $const;
        }

        return $this;
    }

    public function addConstant(string $name, $value): Constant
    {
        return $this->consts[$name] = (new Constant($name))
            ->setValue($value)
            ->setPublic();
    }


    /** @return static */
    public function removeConstant(string $name): self
    {
        unset($this->consts[$name]);
        return $this;
    }


    /**
     * Sets cases to enum
     * @param  EnumCase[]  $cases
     * @return static
     */
    public function setCases(array $cases): self
    {
        (function (EnumCase ...$cases) {})(...array_values($cases));
        $this->cases = [];
        foreach ($cases as $case) {
            $this->cases[$case->getName()] = $case;
        }

        return $this;
    }


    /** @return EnumCase[] */
    public function getCases(): array
    {
        return $this->cases;
    }


    /** Adds case to enum */
    public function addCase(string $name, $value = null): EnumCase
    {
        return $this->cases[$name] = (new EnumCase($name))
            ->setValue($value);
    }


    /** @return static */
    public function removeCase(string $name): self
    {
        unset($this->cases[$name]);
        return $this;
    }


    /**
     * @param  Property[]  $props
     * @return static
     */
    public function setProperties(array $props): self
    {
        (function (Property ...$props) {})(...array_values($props));
        $this->properties = [];
        foreach ($props as $v) {
            $this->properties[$v->getName()] = $v;
        }

        return $this;
    }


    /** @return Property[] */
    public function getProperties(): array
    {
        return $this->properties;
    }


    public function getProperty(string $name): Property
    {
        if (!isset($this->properties[$name])) {
            throw new \Nette\InvalidArgumentException("Property '$name' not found.");
        }

        return $this->properties[$name];
    }


    /**
     * @param  string  $name  without $
     */
    public function addProperty(string $name, $value = null): Property
    {
        return $this->properties[$name] = func_num_args() > 1
            ? (new Property($name))->setValue($value)
            : new Property($name);
    }


    /**
     * @param  string  $name without $
     * @return static
     */
    public function removeProperty(string $name): self
    {
        unset($this->properties[$name]);
        return $this;
    }


    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }


    /**
     * @param  Method[]  $methods
     * @return static
     */
    public function setMethods(array $methods): self
    {
        (function (Method ...$methods) {})(...array_values($methods));
        $this->methods = [];
        foreach ($methods as $m) {
            $this->methods[strtolower($m->getName())] = $m;
        }

        return $this;
    }


    /** @return \Nette\PhpGenerator\Method[] */
    public function getMethods(): array
    {
        $res = [];
        foreach ($this->methods as $m) {
            $res[$m->getName()] = $m;
        }

        return $res;
    }


    public function getMethod(string $name): \Nette\PhpGenerator\Method
    {
        $m = $this->methods[strtolower($name)] ?? null;
        if (!$m) {
            throw new \Nette\InvalidArgumentException("Method '$name' not found.");
        }

        return $m;
    }


    public function addMethod(string $name): \Nette\PhpGenerator\Method
    {
        $method = new \Nette\PhpGenerator\Method($name);
        if ($this->isInterface()) {
            $method->setBody(null);
        } else {
            $method->setPublic();
        }

        return $this->methods[strtolower($name)] = $method;
    }


    /** @return static */
    public function removeMethod(string $name): self
    {
        unset($this->methods[strtolower($name)]);
        return $this;
    }


    public function hasMethod(string $name): bool
    {
        return isset($this->methods[strtolower($name)]);
    }


    /** @throws \Nette\InvalidStateException */
    public function validate(): void
    {
        if ($this->isEnum() && ($this->abstract || $this->final || $this->extends || $this->properties)) {
            throw new \Nette\InvalidStateException("Enum '$this->name' cannot be abstract or final or extends class or have properties.");

        } elseif (!$this->name && ($this->abstract || $this->final)) {
            throw new \Nette\InvalidStateException('Anonymous class cannot be abstract or final.');

        } elseif ($this->abstract && $this->final) {
            throw new \Nette\InvalidStateException("Class '$this->name' cannot be abstract and final at the same time.");
        }
    }


    private function validateNames(array $names): void
    {
        foreach ($names as $name) {
            if (!Helpers::isNamespaceIdentifier($name, true)) {
                throw new \Nette\InvalidArgumentException("Value '$name' is not valid class name.");
            }
        }
    }


    public function __clone()
    {
        $clone = function ($item) { return clone $item; };
        $this->traits = array_map($clone, $this->traits);
        $this->cases = array_map($clone, $this->cases);
        $this->consts = array_map($clone, $this->consts);
        $this->properties = array_map($clone, $this->properties);
        $this->methods = array_map($clone, $this->methods);
    }

    public function printClass(?PhpNamespace $namespace = null): string
    {
        $this->namespace = $this->resolveTypes ? $namespace : null;
        $this->validate();
        $resolver = $this->namespace
            ? [$namespace, 'simplifyType']
            : function ($s) { return $s; };

        $traits = [];
        foreach ($this->getTraitResolutions() as $trait) {
            $resolutions = $trait->getResolutions();
            $traits[] = Helpers::formatDocComment((string) $trait->getComment())
                . 'use ' . $resolver($trait->getName())
                . ($resolutions
                    ? " {\n" . $this->indentation . implode(";\n" . $this->indentation, $resolutions) . ";\n}\n"
                    : ";\n");
        }

        $cases = [];
        foreach ($this->getCases() as $case) {
            $cases[] = Helpers::formatDocComment((string) $case->getComment())
                . self::printAttributes($case->getAttributes())
                . 'case ' . $case->getName()
                . ($case->getValue() === null ? '' : ' = ' . $this->dump($case->getValue()))
                . ";\n";
        }

        $enumType = isset($case) && $case->getValue() !== null
            ? $this->returnTypeColon . Type::getType($case->getValue())
            : '';

        $consts = [];
        foreach ($this->getConstants() as $const) {
            $def = ($const->isFinal() ? 'final ' : '')
                . ($const->getVisibility() ? $const->getVisibility() . ' ' : '')
                . 'const ' . $const->getName() . ' = ';

            $consts[] = Helpers::formatDocComment((string) $const->getComment())
                . self::printAttributes($const->getAttributes())
                . $def
                . $this->dump($const->getValue(), strlen($def)) . ";\n";
        }

        $properties = [];
        foreach ($this->getProperties() as $property) {
            $property->validate();
            $type = $property->getType();
            $def = (($property->getVisibility() ?: 'public')
                . ($property->isStatic() ? ' static' : '')
                . ($property->isReadOnly() && $type ? ' readonly' : '')
                . ' '
                . ltrim($this->printType($type, $property->isNullable()) . ' ')
                . '$' . $property->getName());

            $properties[] = Helpers::formatDocComment((string) $property->getComment())
                . self::printAttributes($property->getAttributes())
                . $def
                . ($property->getValue() === null && !$property->isInitialized()
                    ? ''
                    : ' = ' . $this->dump($property->getValue(), strlen($def) + 3)) // 3 = ' = '
                . ";\n";
        }

        $methods = [];
        foreach ($this->getMethods() as $method) {
            $methods[] = $this->printMethod($method, $namespace);
        }

        $members = array_filter([
            implode('', $traits),
            $this->joinProperties($cases),
            $this->joinProperties($consts),
            $this->joinProperties($properties),
            ($methods && $properties ? str_repeat("\n", $this->linesBetweenMethods - 1) : '')
            . implode(str_repeat("\n", $this->linesBetweenMethods), $methods),
        ]);

        return Strings::normalize(
                Helpers::formatDocComment($this->getComment() . "\n")
                . self::printAttributes($this->getAttributes())
                . ($this->isAbstract() ? 'abstract ' : '')
                . ($this->isFinal() ? 'final ' : '')
                . ($this->getClassName() ? $this->getType() . ' ' . $this->getClassName() . $enumType . ' ' : '')
                . ($this->getExtends() ? 'extends ' . implode(', ', array_map($resolver, (array) $this->getExtends())) . ' ' : '')
                . ($this->getImplements() ? 'implements ' . implode(', ', array_map($resolver, $this->getImplements())) . ' ' : '')
                . ($this->getClassName() ? "\n" : '') . "{\n"
                . ($members ? $this->indent(implode("\n", $members)) : '')
                . '}'
            ) . ($this->getClassName() ? "\n" : '');
    }


    public function printNamespace(PhpNamespace $namespace): string
    {
        $this->namespace = $this->resolveTypes ? $namespace : null;
        $name = $namespace->getName();
        $uses = $this->printUses($namespace)
            . $this->printUses($namespace, PhpNamespace::NameFunction)
            . $this->printUses($namespace, PhpNamespace::NameConstant);

        $items = [];
        foreach ($namespace->getClasses() as $class) {
            $items[] = $this->printClass($class, $namespace);
        }

        foreach ($namespace->getFunctions() as $function) {
            $items[] = $this->printFunction($function, $namespace);
        }

        $body = ($uses ? $uses . "\n" : '')
            . implode("\n", $items);

        if ($namespace->hasBracketedSyntax()) {
            return 'namespace' . ($name ? " $name" : '') . "\n{\n"
                . $this->indent($body)
                . "}\n";

        } else {
            return ($name ? "namespace $name;\n\n" : '')
                . $body;
        }
    }


    public function printFile(PhpFile $file): string
    {
        $namespaces = [];
        foreach ($file->getNamespaces() as $namespace) {
            $namespaces[] = $this->printNamespace($namespace);
        }

        return Strings::normalize(
                "<?php\n"
                . ($file->getComment() ? "\n" . Helpers::formatDocComment($file->getComment() . "\n") : '')
                . "\n"
                . ($file->hasStrictTypes() ? "declare(strict_types=1);\n\n" : '')
                . implode("\n\n", $namespaces)
            ) . "\n";
    }


    protected function printUses(PhpNamespace $namespace, string $of = PhpNamespace::NameNormal): string
    {
        $prefix = [
            PhpNamespace::NameNormal => '',
            PhpNamespace::NameFunction => 'function ',
            PhpNamespace::NameConstant => 'const ',
        ][$of];
        $name = $namespace->getName();
        $uses = [];
        foreach ($namespace->getUses($of) as $alias => $original) {
            $uses[] = Helpers::extractShortName($original) === $alias
                ? "use $prefix$original;\n"
                : "use $prefix$original as $alias;\n";
        }

        return implode('', $uses);
    }


    /**
     * @param Closure|GlobalFunction|\Nette\PhpGenerator\Method $function
     */
    protected function printParameters($function, int $column = 0): string
    {
        $params = [];
        $list = $function->getParameters();
        $special = false;

        foreach ($list as $param) {
            $param->validate();
            $variadic = $function->isVariadic() && $param === end($list);
            $type = $param->getType();
            $promoted = $param instanceof PromotedParameter ? $param : null;
            $params[] =
                ($promoted ? Helpers::formatDocComment((string) $promoted->getComment()) : '')
                . ($attrs = self::printAttributes($param->getAttributes(), true))
                . ($promoted ?
                    ($promoted->getVisibility() ?: 'public')
                    . ($promoted->isReadOnly() && $type ? ' readonly' : '')
                    . ' ' : '')
                . ltrim($this->printType($type, $param->isNullable()) . ' ')
                . ($param->isReference() ? '&' : '')
                . ($variadic ? '...' : '')
                . '$' . $param->getName()
                . ($param->hasDefaultValue() && !$variadic ? ' = ' . $this->dump($param->getDefaultValue()) : '');

            $special = $special || $promoted || $attrs;
        }

        $line = implode(', ', $params);

        return count($params) > 1 && ($special || strlen($line) + $column > $this->wrapLength)
            ? "(\n" . $this->indent(implode(",\n", $params)) . ($special ? ',' : '') . "\n)"
            : "($line)";
    }


    protected function printType(?string $type, bool $nullable): string
    {
        if ($type === null) {
            return '';
        }

        if ($this->namespace) {
            $type = $this->namespace->simplifyType($type);
        }

        if ($nullable && strcasecmp($type, 'mixed')) {
            $type = strpos($type, '|') === false
                ? '?' . $type
                : $type . '|null';
        }

        return $type;
    }


    /**
     * @param Closure|GlobalFunction|Method  $function
     */
    private function printReturnType($function): string
    {
        return ($tmp = $this->printType($function->getReturnType(), $function->isReturnNullable()))
            ? $this->returnTypeColon . $tmp
            : '';
    }


    private function printAttributes(array $attrs, bool $inline = false): string
    {
        if (!$attrs) {
            return '';
        }

        $this->dumper->indentation = $this->indentation;
        $items = [];
        foreach ($attrs as $attr) {
            $args = $this->dumper->format('...?:', $attr->getArguments());
            $args = Helpers::simplifyTaggedNames($args, $this->namespace);
            $items[] = $this->printType($attr->getName(), false) . ($args ? "($args)" : '');
        }

        return $inline
            ? '#[' . implode(', ', $items) . '] '
            : '#[' . implode("]\n#[", $items) . "]\n";
    }


    /** @return static */
    public function setTypeResolving(bool $state = true): self
    {
        $this->resolveTypes = $state;
        return $this;
    }


    protected function indent(string $s): string
    {
        $s = str_replace("\t", $this->indentation, $s);
        return Strings::indent($s, 1, $this->indentation);
    }


    protected function dump($var, int $column = 0): string
    {
        $this->dumper->indentation = $this->indentation;
        $this->dumper->wrapLength = $this->wrapLength;
        $s = $this->dumper->dump($var, $column);
        $s = Helpers::simplifyTaggedNames($s, $this->namespace);
        return $s;
    }


    private function joinProperties(array $props): string
    {
        return $this->linesBetweenProperties
            ? implode(str_repeat("\n", $this->linesBetweenProperties), $props)
            : preg_replace('#^(\w.*\n)\n(?=\w.*;)#m', '$1', implode("\n", $props));
    }
    public function printMethod(\Nette\PhpGenerator\Method $method, PhpNamespace $namespace = null): string{
        $f = $this->existingFile;
        try {
            if ($f->methodInheritedFromTrait($method->getName())) {
                return "";
            }
        } catch (QMFileNotFoundException $e) {
            QMLog::error("Could not find trait: " . $e->getMessage());
        }
        $method->validate();
        $line = ($method->isAbstract() ? 'abstract ' : '')
            . ($method->isFinal() ? 'final ' : '')
            . ($method->getVisibility() ? $method->getVisibility() . ' ' : '')
            . ($method->isStatic() ? 'static ' : '')
            . 'function '
            . ($method->getReturnReference() ? '&' : '')
            . $method->getName();
        $returnType = $this->printReturnType($method, $namespace);

        $str = Helpers::formatDocComment($method->getComment() . "\n")
            . self::printAttributes($method->getAttributes(), $namespace)
            . $line
            . ($params = $this->printParameters($method, strlen($line) + strlen($returnType) + strlen($this->indentation) + 2)) // 2 = parentheses
            . $returnType
            . ($method->isAbstract() || $method->getBody() === null
                ? ";\n"
                : ''
                . "{\n"
                . $this->indent(ltrim(rtrim($method->getBody()) . "\n"))
                . "}\n");
        return $str;
    }

}
