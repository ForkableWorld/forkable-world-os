<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\QMButton;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\VariableCategory;
use App\Storage\Memory;
use App\Types\QMStr;
use Facade\IgnitionContracts\Solution;
class ViewModelsInMemorySolution extends AbstractSolution implements Solution {
	/**
	 * @var \Throwable
	 */
	private $exception;
	public function __construct(\Throwable $e){
		$this->exception = $e;
	}
	public function getSolutionTitle(): string{
		return "View Related Models";
	}
	public function getSolutionDescription(): string{
		return "See details about all the models stored in memory at the time of the exception";
	}
	public function getDocumentationLinks(): array{
		$arr = ViewModelsInMemorySolution::generateModelLinks();
		if(empty($arr)){
			le("No related model links!");
		}
		return $arr;
	}
	/**
	 * @return array
	 */
	public static function generateModelLinks(): array{
		$classes = BaseModel::getClassNames();
		$arr = [];
		foreach($classes as $class){
			$short = QMStr::toShortClassName($class);
			$table = $class::TABLE;
			if(!$table){
				QMLog::debug("Please set TABLE on $class");
				continue;
			}
			if($table === VariableCategory::TABLE){
				continue;
			}
			if($model = Memory::getLast($table)){
				if(method_exists($model, 'getButton')){
					/** @var QMButton $b */
					try {
						$b = $model->getButton();
						$arr["Open ".$b->getTitleAttribute()." $short"] = $b->getUrl();
					} catch (\Throwable $e) {
						QMLog::error("Could not get button from $model ".get_class($model)." because ".
						             $e->getMessage());
					}
				}
			}
		}
		return $arr;
	}
	/**
	 * @return \Throwable
	 */
	public function getException(): \Throwable{
		return $this->exception;
	}
}
