<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Traits\FileTraits\IsPHPFileModel;
use App\Traits\LoggerTrait;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\MethodModel;
use ReflectionMethod;
class QMMethodModel extends MethodModel {
	use LoggerTrait;
	private $classModel;
	/**
	 * QMMethodModel constructor.
	 * @param ReflectionMethod $rf
	 * @param IsPHPFileModel|PhpClassFile $cm
	 */
	public function __construct($rf, PhpClassFile $cm){
		$this->classModel = $cm;
		parent::__construct($rf->getName(), 'public');
		$this->setStatic($this->isStatic());
		if($rf->isPublic()){
			$this->setAccess("public");
		}
		if($rf->isProtected()){
			$this->setAccess("protected");
		}
		if($rf->isPrivate()){
			$this->setAccess("private");
		}
		$this->setAbstract($rf->isAbstract());
		$this->setDocBlock(new DocBlockModel($rf->getDocComment()));
		$this->setFinal($rf->isFinal());
	}
	public function findBody(): string{
		$func = $this->getReflectionMethod();
		$f = $func->getFileName();
		$start_line = $func->getStartLine() - 1;
		$end_line = $func->getEndLine();
		$length = $end_line - $start_line;
		$source = file($f);
		$source = implode('', array_slice($source, 0, count($source)));
		// $source = preg_split("/(\n|\r\n|\r)/", $source);
		$source = preg_split("/" . PHP_EOL . "/", $source);
		$body = '';
		for($i = $start_line; $i < $end_line; $i++){
			$body .= "{$source[$i]}\n";
		}
		return $body;
	}
	/**
	 * @return PhpClassFile
	 */
	public function getClassModel(): PhpClassFile{
		return $this->classModel;
	}
	/**
	 * @return ReflectionMethod
	 */
	private function getReflectionMethod(): ReflectionMethod{
		$func = new ReflectionMethod($this->getClassModel()->getClassName(), $this->getName());
		return $func;
	}
	public function isInherited(): bool{
		$dc = $this->getDeclaringClass();
		$methodName = $this->getName();
		if($dc->hasMethod($methodName)){
			return true;
		}
		$parent = $this->getParentClass();
		if($parent && $parent->hasMethod($methodName)){
			return true;
		}
		return false;
	}
	public function getType(): string{
		return $this->isStatic() ? "::" : "->";
	}
	public function renderCall(): string{
		if($this->isStatic()){
			return $this->getClassName() . "::" . $this->getName() . "(" . $this->getArguments() . ")";
		}
		return "(new " . $this->getClassName() . ")->" . $this->getName() . "(" . $this->getArguments() . ")";
	}
	public function __toString(){
		return $this->getClassName() . $this->getType() . $this->getName();
	}
	/**
	 * @return \ReflectionClass
	 */
	public function getDeclaringClass(): \ReflectionClass{
		$rm = $this->getReflectionMethod();
		return $rm->getDeclaringClass();
	}
	/**
	 * @return false|\ReflectionClass
	 */
	public function getParentClass(){
		$dc = $this->getDeclaringClass();
		return $dc->getParentClass();
	}
	public function getClassName(): string{
		$name = $this->getClassModel()->getClassName();
		return $name;
	}
}
