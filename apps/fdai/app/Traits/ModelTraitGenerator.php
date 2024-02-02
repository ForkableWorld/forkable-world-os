<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Types\QMStr;
class ModelTraitGenerator {
	/**
	 * @var BaseModel
	 */
	private $model;
	/**
	 * @var string
	 */
	private $code;
	public function __construct(BaseModel $m){
		$this->model = $m;
	}
	public static function generateAll(){
		$classes = BaseModel::getClasses();
		foreach($classes as $class){
			self::generateByClass($class);
		}
	}
	public function generate(): void{
		$model = $this->getModel();
		$props = $model->getPropertyModels();
		foreach($props as $property){
			$name = $property->name;
			if(!$this->traitHasGetter($name)
				//&& !$this->laravelHasGetter($name)
				//&& !$this->slimHasGetter($name)
			){
				$this->addGetter($name);
			}
			if(!$this->traitHasSetter($name) && !$this->laravelHasSetter($name)//&& !$this->slimHasSetter($name)
			){
				$this->addSetter($name);
			}
		}
		$this->save();
		$model->addTrait($this->getTraitClass());
		$this->getSlimClass();
	}
	public static function generateByClass(string $class): void{
		$generator = new static(new $class);
		if(!$generator->getSlimClass()){
			QMLog::info("No slim class for $class");
			return;
		}
		$generator->generate();
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this->model;
	}
	private function getNameSpace(): string{
		return 'App\Traits\ModelTraits';
	}
	private function getTraitClass(): string{
		return $this->getNameSpace() . "\\" . $this->getShortTraitClass();
	}
	public function getTraitCode(): string{
		if($this->code){
			return $this->code;
		}
		$path = $this->getTraitPath();
		try {
			$contents = FileHelper::getContents($path);
		} catch (QMFileNotFoundException $e) {
			$shortParent = $this->getShortParentClass();
			$ns = $this->getNameSpace();
			try {
				$contents = "<?php
namespace $ns;
use App\Models\\$shortParent;
trait {$shortParent}Trait
{
}";
			} catch (\Throwable $e) {
				le($e);
				throw new \LogicException();
			}
		}
		return $this->code = $contents;
	}
	private function getTraitPath(): string{
		$class = $this->getShortTraitClass();
		return "app/Traits/ModelTraits/" . $class . ".php";
	}
	private function getShortParentClass(): string{
		return $this->getModel()->getShortClassName();
	}
	private function getShortTraitClass(): string{
		return $this->getShortParentClass() . "Trait";
	}
	private function addGetter($name){
		$this->appendCode($this->getGetter($name));
	}
	private function addSetter($name){
		$this->appendCode($this->getSetter($name));
	}
	private function getPropertyModel($name): BaseProperty{
		$prop = $this->getModel()->getPropertyModel($name);
		if(!$prop){
			le("No $name property on " . get_class($this->getModel()));
		}
		return $prop;
	}
	private function getGetter($name): string{
		return $this->getPropertyModel($name)->getGetterCode();
	}
	private function getSetter($name): string{
		return $this->getPropertyModel($name)->getSetterCode(true);
	}
	/**
	 * @param string $str
	 */
	private function appendCode(string $str): void{
		$this->code = QMStr::appendCode($this->getTraitCode(), $str);
	}
	private function save(){
		try {
			$code = QMStr::removeEmptyLines($this->getTraitCode());
			FileHelper::writeByFilePath($this->getTraitPath(), $code);
		} catch (InvalidFilePathException $e) {
			le($e);
		}
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function traitHasGetter(string $name){
		$code = $this->getTraitCode();
		return stripos($code, "function " . $this->getGetterFunctionName($name));
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function traitHasSetter(string $name): bool{
		$code = $this->getTraitCode();
		return stripos($code, "function " . $this->getSetterFunctionName($name));
	}
	private function getSetterFunctionName(string $name): string{
		return $this->getPropertyModel($name)->getSetterFunctionName();
	}
	private function getGetterFunctionName(string $name): string{
		return $this->getPropertyModel($name)->getGetterFunctionName();
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function slimHasGetter(string $name): bool{
		$class = $this->getSlimClass();
		if(!$class){
			return false;
		}
		return method_exists($class, $this->getGetterFunctionName($name));
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function slimHasSetter(string $name){
		$class = $this->getSlimClass();
		if(!$class){
			return false;
		}
		return method_exists($class, $this->getSetterFunctionName($name));
	}
	private function getSlimClass(): ?string{
		$model = $this->getModel();
		if(method_exists($model, 'getSlimClass')){
			$sc = $model::getSlimClass();
			return $sc;
		}
		return null;
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function laravelHasGetter(string $name): bool{
		$class = $this->getModel();
		if(!$class){
			return false;
		}
		return method_exists($class, $this->getGetterFunctionName($name));
	}
	/**
	 * @param string $name
	 * @return false|int
	 */
	private function laravelHasSetter(string $name){
		$class = $this->getModel();
		if(!$class){
			return false;
		}
		return method_exists($class, $this->getSetterFunctionName($name));
	}
}
