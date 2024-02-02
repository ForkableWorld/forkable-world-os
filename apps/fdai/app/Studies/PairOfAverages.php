<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
class PairOfAverages {
    public float $causeVariableAverageValue;
    public float $effectVariableAverageValue;
    protected int $causeUserVariableId;
    protected int $effectUserVariableId;
    protected GlobalVariableRelationship $aggregateCorrelation;
    protected int $number;
	/**
	 * @var \App\Models\UserVariableRelationship
	 */
	private UserVariableRelationship $correlation;
	/**
	 * @param \App\Models\UserVariableRelationship $correlation
	 */
	public function __construct(UserVariableRelationship $correlation) {
		$this->correlation = $correlation;
		$this->causeUserVariableId = $correlation->cause_user_variable_id;
		$this->effectUserVariableId = $correlation->effect_user_variable_id;
		$this->number = $correlation->user_id;
		$this->setGlobalVariableRelationship($correlation->getGlobalVariableRelationship());
		$this->causeVariableAverageValue = $correlation->getCauseUserVariable()->mean;
		$this->effectVariableAverageValue = $correlation->getEffectUserVariable()->getMean();
    }
    /**
     * @return int
     */
    public function getUserId(): ?int{
        return $this->number;
    }
    /**
     * @return int
     */
    public function getCauseVariableId(): int{
        return $this->getGlobalVariableRelationship()->getCauseVariableId();
    }
    /**
     * @return int
     */
    public function getEffectVariableId(): int{
        return $this->getGlobalVariableRelationship()->getEffectVariableId();
    }
    /**
     * @return GlobalVariableRelationship
     */
    public function getGlobalVariableRelationship(): ?GlobalVariableRelationship{
        return $this->aggregateCorrelation;
    }
	/**
	 * @param \App\Models\GlobalVariableRelationship $aggregateCorrelation
	 */
    public function setGlobalVariableRelationship(GlobalVariableRelationship $aggregateCorrelation): void{
        $this->aggregateCorrelation = $aggregateCorrelation;
    }
    /**
     * @return int
     */
    public function getCauseUserVariableId(): int {
        return $this->causeUserVariableId;
    }
    /**
     * @return int
     */
    public function getEffectUserVariableId(): int {
        return $this->effectUserVariableId;
    }

    /**
     * @return UserVariableRelationship
     */
    public function getCorrelation(): UserVariableRelationship
    {
        return $this->correlation;
    }
}
