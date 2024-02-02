<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\VariableCategory;
use Illuminate\Support\Str;
abstract class HardCodedQMClass extends PhpClassFile {
	/**
	 * @var BaseModel
	 */
	private $model;
	/**
	 * @return BaseModel
	 */
	abstract protected static function getBaseModelClass(): string;
	public function __construct(BaseModel $model){
		$this->model = $model;
		parent::__construct($this->getPath());
	}
	/**
	 * @return VariableCategory|null
	 */
	protected function getBaseModel(): ?BaseModel{
		return $this->model;
	}
	public function getFileName(): string{
		$model = $this->getBaseModel();
		$shortClass = Str::studly($model->getNameAttribute());
		return $shortClass . $model->getShortClassName();
	}
	public function update(){
		$cat = $this->getBaseModel();
		foreach($cat->getPropertyModels() as $property){
			$value = $property->getHardCodedValue();
			try {
				$this->addConstant($property->name, $value);
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				$this->addConstant($property->name, $value);
			}
		}
	}
	public function updateFromDB(){
		$this->update();
	}
	public static function getHardCodedValue($id, string $key){
		$class = static::getBaseModelClass();
		$one = $class::findInMemoryOrDB($id);
		$prop = $one->getPropertyModel($key);
		$value = $prop->getHardCodedValue();
		return $value;
	}
	public static function updateAll(){
		$class = static::getBaseModelClass();
		$all = $class::all();
		foreach($all as $one){
			$me = new static($one);
			$me->update();
		}
	}
}
