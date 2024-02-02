<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasMany;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Utils\Stats;
trait HasManyCorrelations {
	/**
	 * @param string $attribute
	 * @return array
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function getUserVariableRelationshipValues(string $attribute): array{
		$userVariableRelationships = $this->getCorrelations();
		if(!$userVariableRelationships->count()){
			throw new NoUserVariableRelationshipsToAggregateException($this);
		}
		return $userVariableRelationships->pluck($attribute)->all();
	}
	/**
	 * @param string $attribute
	 * @return float
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function summedUserVariableRelationshipValue(string $attribute): float{
		$values = $this->getUserVariableRelationshipValues($attribute);
		return Stats::sum($values);
	}
	/**
	 * @param string $attribute
	 * @return float
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function averageUserVariableRelationshipValue(string $attribute): float{
		$values = $this->getUserVariableRelationshipValues($attribute);
		return Stats::average($values);
	}
	/**
	 * @param string $attribute
	 * @return float
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function minimumUserVariableRelationshipValue(string $attribute): float{
		$values = $this->getUserVariableRelationshipValues($attribute);
		return min($values);
	}
	/**
	 * @param string $attribute
	 * @return float
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function weightedAvgFromUserVariableRelationships(string $attribute): ?float{
		$correlations = $this->getCorrelations();
		if(!$correlations->count()){
			throw new NoUserVariableRelationshipsToAggregateException($this);
		}
		$numerators = [];
		$avgStatisticalSignificance = $correlations->avg('statistical_significance');
		$weightedValues = [];
		/** @var UserVariableRelationship $c */
		foreach($correlations as $c){
			$statisticalSignificanceAttribute = $c->getStatisticalSignificanceAttribute();
			if(!$statisticalSignificanceAttribute){
				$c->logError("No statistical significance attribute for correlation $c->id");
				continue;
			}
			$weight = $statisticalSignificanceAttribute / $avgStatisticalSignificance;
			$val = $c->getAttribute($attribute);
			if($val === null){
				$c->logError("No value for attribute $attribute for correlation $c->id");
				continue;
			}
			$weightedValues[] = $val * $weight;
		}
		if(!$weightedValues){
			$this->logError("No weighted values for attribute $attribute");
			throw new NoUserVariableRelationshipsToAggregateException($this);
		}
		$weightedAvg = Stats::average($weightedValues);
		return $weightedAvg;
	}
}
