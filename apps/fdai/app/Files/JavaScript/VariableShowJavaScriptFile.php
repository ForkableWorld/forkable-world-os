<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\JavaScript;
use App\Folders\DynamicFolder;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
use App\Properties\Base\BaseUpdatedAtProperty;
class VariableShowJavaScriptFile extends ShowJavaScriptFile {
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::FOLDER_PUBLIC_JS . "/variables";
	}
	public function getData(): array{
		$v = $this->getVariable();
		$arr = $v->toNonNullArrayFast();
		$v->getOrSetHighchartConfigs();
		$arr['charts'] = $v->getChartGroup();
		$arr['predictors'] = $this->getPredictorData();
		$arr['outcomes'] = $this->getOutcomeData();
		$arr['variableCategory'] = $v->getVariableCategory();
		unset($arr[BaseUpdatedAtProperty::NAME]);
		//unset($arr['charts']);
		return $arr;
	}
	public function getVariable(): Variable{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getModel();
	}
	/**
	 * @return array
	 * @noinspection DuplicatedCode
	 */
	public function getPredictorData(): array{
		$v = $this->getVariable();
		$pData = [];
		$onlyUpVoted = false;
		if($onlyUpVoted){
			$predictors = $v->getUpVotedPublicPredictors();
			if($predictors->count() < 100){
				$predictors = $predictors->concat($v->getPublicPredictors())->unique(fn($item) => $item->getId());
			}
		} else{
			$predictors = $v->getPublicPredictors();
		}
		$maxChange = $predictors->max(function($c){
			/** @var UserVariableRelationship $c */
			return abs($c->effect_follow_up_percent_change_from_baseline);
		});
		/** @var GlobalVariableRelationship $correlation */
		foreach($predictors as $correlation){
			$pData[] = $correlation->getPredictorButtonData($maxChange);
		}
		return $pData;
	}
	/**
	 * @return array
	 * @noinspection DuplicatedCode
	 */
	public function getOutcomeData(): array{
		$v = $this->getVariable();
		$pData = [];
		$onlyUpVoted = false;
		if($onlyUpVoted){
			$outcomes = $v->getUpVotedPublicOutcomes();
			if($outcomes->count() < 100){
				$outcomes = $outcomes->concat($v->getPublicOutcomes())->unique(fn($item) => $item->getId());
			}
		} else{
			$outcomes = $v->getPublicOutcomes();
		}
		$maxChange = $outcomes->max(function($c){
			/** @var UserVariableRelationship|GlobalVariableRelationship $c */
			return abs($c->effect_follow_up_percent_change_from_baseline);
		});
		/** @var GlobalVariableRelationship $correlation */
		foreach($outcomes as $correlation){
            $correlation->logId();
            $correlation->logName();
			$pData[] = $correlation->getOutcomeButtonData($maxChange);
		}
		return $pData;
	}
}
