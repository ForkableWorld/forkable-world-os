<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\JavaScript;
use App\Models\BaseModel;
use App\Types\QMArr;
use App\Types\QMStr;
use Illuminate\Support\Collection;
abstract class ShowJavaScriptFile extends AbstractJavaScriptFile {
	/**
	 * @var BaseModel
	 */
	private $model;
	public function __construct(BaseModel $model){
		$this->model = $model;
		parent::__construct("public/js/" . $model->getSlugifiedClassName() . "/" . $model->getSlug() . ".js");
	}
	abstract public function getData();
	/**
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this->model;
	}
	public function generateContents(): string{
		$model = $this->getModel();
		return self::toJS($model->getShortClassName(), $this->getData());
	}
	public static function toJS(string $class, array $data): string{
		foreach($data as $key => $value){
			if($value instanceof BaseModel){
				$data[$key] = $value->toNonNullArrayFast();
			} elseif($value instanceof Collection){
				$data[$key] = QMArr::toArrays($value);
			} else{
				$data[$key] = $value;
			}
		}
		$class = QMStr::toShortClassName($class);
		$varName = QMStr::camelize($class);
		$json = QMStr::prettyJsonEncode($data);
		return "
var model = $json
var host = window.location.host;
if(host === \"\"){host = \"local.quantimo.do\"} // We just opened the file locally
if(host.indexOf('quantimo.do') !== -1 || host.indexOf('crowdsourcingcures.org') !== -1){
    var str = JSON.stringify(model)
        .replaceAll('staging.quantimo.do', host)
        .replaceAll('local.quantimo.do', host)
        .replaceAll('app.quantimo.do', host);
    $varName = JSON.parse(str);
}
";
	}
	public static function generate(BaseModel $model): string{
		return (new static($model))->generateContents();
	}
}
