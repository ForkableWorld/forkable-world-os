<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Models\CtCondition;
use App\Models\CtConditionTreatment;
use App\Models\CtTreatment;
use App\Storage\DB\Writable;
class WhatWeDoNotKnowSeries extends NodeSeries {
	protected static $instance;
	public $id = "what-we-do-not-know";
	public function __construct($limit = 10){
		parent::__construct('What is the optimal daily value?');
		$pairs = [];
		$ct =
			Writable::getBuilderByTable('ct_condition_treatment')->orderBy('popularity', 'desc')->limit($limit)->get();
		/** @var CtConditionTreatment $row */
		foreach($ct as $i => $row){
			$cause = CtTreatment::findInMemoryOrDB($row->treatment_id)->name;
			$cause = addslashes($cause);
			$effect = CtCondition::findInMemoryOrDB($row->condition_id)->name;
			$effect = addslashes($effect);
			$this->addDataPoint($cause, $effect, $i % 5,
				"What value of $cause is most likely to lead to optimal $effect?");
		}
		$this->addNodes();
		return $pairs;
	}
	public static function get(): self{
		if(static::$instance){
			return static::$instance;
		}
		return static::$instance = new static();
	}
}
