<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\DevOps\XDebug;
use App\Exceptions\InvalidAttributeException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Properties\BaseProperty;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\QMStr;
use App\Utils\AppMode;
use jc21\CliTable;
use Tests\QMAssert;
trait HasCalculatedAttributes {
	protected $calculated = [];
	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getCalculatedAttribute(string $key){
		return $this->calculated[$key]['after'] ?? null;
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function calculateAttribute(string $key){
		$after = $this->getCalculatedAttribute($key);
		if($after !== null){
			$this->logInfo("Already calculated $key so skipping...");
			return $after;
		}
		$before = $this->getRawAttribute($key);
		$start = microtime(true);
		/** @var IsCalculated|BaseProperty $prop */
		$prop = $this->getPropertyModel($key);
		$after = $prop->calculate($this); // TODO: $prop->calculateAndSet($this);
		$this->setCalculatedAttribute($key, $before, $after);
		$this->setAttribute($key, $after);
		if(QMLogLevel::isDebug() || !AppMode::isUnitOrStagingUnitTest()){ // Causes to much variance in test logs
			$this->calculated[$key]['duration'] = microtime(true) - $start;
		}
		$slimVal = $this->getAttribute($prop->name);
		$l = $this->l();
		$lVal = $l->getAttribute($prop->name);
		if(!is_array($slimVal) && !is_array($lVal) && !is_array($before)){
			QMAssert::assertSimilar($slimVal, $lVal,
				"Slim value for $prop->name ($slimVal) should equal laravel value ($lVal). \n" .
				"Value before calculation was " . $before);
		}
		return $after;
	}
	public function getAnalyzeUrl(array $params = []): string{
		$params[QMRequest::PARAM_ANALYZE] = 1;
		return $this->getUrl($params);
	}
	/**
	 * @return array
	 */
	public function calculateAttributes(): array{
		$this->logInfo(__FUNCTION__." for $this");
		$props = $this->getCalculatedProperties();
		foreach($props as $prop){$this->calculateProperty($prop);}
		$calculated = $this->calculated;
		$this->logChangeTable($calculated);
		$this->validatePostCalculation();
		return $calculated;
	}
	/**
	 * @return array
	 */
	public function getCalculatedProperties(): array{
		$props = $this->getPropertyModels();
		$calculated = [];
		foreach($props as $prop){
			if(property_exists($prop, 'isCalculated') && $prop->isCalculated){
				$calculated[$prop->name] = $prop;
			}
		}
		return $calculated;
	}
	/**
	 * @param array $calculated
	 */
	private function logChangeTable(array $calculated): void{
		$table = new CliTable;
		$table->addField("Field", 0);
		$table->addField('Before', 1);
		$table->addField('After', 2);
		$changes = [];
		foreach($calculated as $key => $arr){
			if($arr['before'] !== $arr['after']){
				if($key === "charts"){
					continue;
				}
				$changes[] = [$key, QMStr::toString($arr['before'], 10), QMStr::toString($arr['after'], 10)];
				$diff[$key] = $arr;
			}
		}
		$table->injectData($changes);
		ConsoleLog::info("\n".$table->get(), [], false);
	}
	/**
	 * @param $prop
	 * @return \Exception|\Throwable
	 */
	private function calculateProperty($prop){
		try {
			return $this->calculateAttribute($prop->name);
		} catch (\Throwable $e) {
			if(XDebug::active()){
				$this->calculateAttribute($prop->name);
			}
			QMLog::info($e->getMessage() . "\n" . $this->getAnalyzeUrl());
			if($throw = true){
				le($e);
			} // Set false if you want a list of all props that need implementation
		}
		return $e;
	}
	private function validatePostCalculation(): void{
		try {
			$this->checkNotNullAttributes();
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$this->checkNotNullAttributes();
		}
	}
	/**
	 * @param string $key
	 * @param $before
	 * @param $after
	 */
	public function setCalculatedAttribute(string $key, $before, $after): void{
		$this->calculated[$key] = ['before' => $before, 'after' => $after];
	}
	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getCalculated(string $key){
		return $this->calculated[$key]['after'] ?? null;
	}
}
