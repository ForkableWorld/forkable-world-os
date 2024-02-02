<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Models\GlobalVariableRelationship;
use App\Storage\DB\Writable;
trait IsAverageOfCorrelations {
	use IsAnalyzableProperty;
	/**
	 * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
	 * @return float
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public static function calculate($model): float{
		return $model->averageUserVariableRelationshipValue(static::NAME);
	}
	public static function updateAll(){
		$prop = new static();
		$me = $prop->name;
		Writable::statementStatic("
            update global_variable_relationships ac
                join (
                    SELECT
                        c.cause_variable_id,
                        c.effect_variable_id,
                        AVG(c.$me) AS avg
                    FROM user_variable_relationships c
                    GROUP BY c.cause_variable_id, c.effect_variable_id
                ) as sel
                on sel.cause_variable_id = ac.cause_variable_id and sel.effect_variable_id = ac.effect_variable_id
                set ac.$me = sel.avg
        ");
	}
	/** @noinspection PhpUnusedLocalVariableInspection
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public static function fixNulls(){
		$before = static::logNulls("before");
		$prop = new static();
		$me = $prop->name;
		Writable::statementStatic("
            update global_variable_relationships ac
                join (
                    SELECT
                        c.cause_variable_id,
                        c.effect_variable_id,
                        AVG(c.$me) AS avg
                    FROM user_variable_relationships c
                    GROUP BY c.cause_variable_id, c.effect_variable_id
                ) as sel
                on sel.cause_variable_id = ac.cause_variable_id and sel.effect_variable_id = ac.effect_variable_id
                set ac.$me = sel.avg
                where ac.$me is null
        ");
		$after = static::logNulls("before");
	}
}
