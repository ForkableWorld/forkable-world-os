<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\Admin\PHPStormButton;
use App\Files\FileHelper;
use App\Traits\FileTraits\IsSolution;
use App\Types\QMStr;
use ReflectionClass;
abstract class CreateClass extends BaseRunnableSolution {
	use IsSolution;
	public $newClass;
	public function __construct(){ }
	/**
	 * @param string|null $shortClassName
	 * @return string
	 */
	public static function generate(string $shortClassName = null): string{
		if($shortClassName){ // Convert to short if necessary
			$shortClassName = QMStr::toShortClassName($shortClassName);
		}
		$stubShort = (new static())->getStubShortClassName();
		$stubFull = (new static())->getStubClassName();
		if(!$shortClassName){
			$shortClassName = "New".str_replace("Stub", "", $stubShort);
		}
		$newFull = str_replace($stubShort, $shortClassName, $stubFull);
		return (new static())->run(['newClass' => $newFull]);
	}
	/**
	 * @param array $parameters
	 * @return string URL to file
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function run(array $parameters = []): string{
		if(isset($parameters['newClass'])){
			$this->newClass = $parameters['newClass'];
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		$contents = FileHelper::getContents($this->getPathToStub());
		$path = $this->getPathToNewClass();
		$contents = $this->replaceStubPlaceholders($contents);
		$path = FileHelper::writeByFilePath($path, $contents);
		return PHPStormButton::redirectUrl($path);
	}
	/**
	 * @param string|null $contents
	 * @return string|string[]|null
	 */
	public function replaceStubPlaceholders(string $contents){
		$new = $this->getNewShortClassName();
		$contents = str_replace($this->getStubShortClassName(), $new, $contents);
		return $contents;
	}
	public function getRunButtonText(): string{
		return "Generate ".$this->getNewShortClassName();
	}
	public function getNewShortClassName(): string{
		$full = $this->getNewFullClassName();
		return QMStr::toShortClassName($full);
	}
	public function getNewFullClassName(): string{
		if($c = $this->newClass){
			return $c;
		}
		return $this->getNewClassNamespace().'\\'.$this->getNewShortClassName();
	}
	public function getNewClassNamespace(): string{
		$a = new ReflectionClass($this->getStubClassName());
		return $a->getNamespaceName();
	}
	abstract public function getStubClassName(): string;
	public function getRunParameters(): array{
		return ['newClass' => $this->getNewFullClassName()];
	}
	public function getSolutionTitle(): string{
		return "Create ".$this->getNewShortClassName();
	}
	public function getSolutionDescription(): string{
		return "Please implement handler and BaseException with a Solution Title for this exception";
	}
	public function getDocumentationLinks(): array{
		$title = $this->getNewShortClassName();
		$links["New $title to Edit"] = $this->getRedirectUrl();
		return $links;
	}
	public function getRedirectUrl(): string{
		return PHPStormButton::redirectUrl($this->getPathToNewClass());
	}
	public function getPathToNewClass(): string{
		$stubShort = $this->getStubShortClassName();
		$newShort = $this->getNewShortClassName();
		$path = $this->getPathToStub();
		return str_replace($stubShort, $newShort, $path);
	}
	public function getStubShortClassName(): string{
		return QMStr::toShortClassName($this->getStubClassName());
	}
	public function getPathToStub(): string{
		$a = new ReflectionClass($this->getStubClassName());
		return $a->getFileName();
	}
	public function getPreviousExceptionShortClassName(): string{
		return QMStr::toShortClassName($this->newClass);
	}
	abstract public function getBaseClassName(): string;
}
