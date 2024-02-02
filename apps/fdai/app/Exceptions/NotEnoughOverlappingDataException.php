<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\VariableRelationships\QMUserVariableRelationship;
class NotEnoughOverlappingDataException extends NotEnoughDataException {
    /**
     * @var QMUserVariableRelationship
     */
    public $userVariableRelationship;
    /**
     * NotEnoughBaselineDataException constructor.
     * @param QMUserVariableRelationship $c
     */
    public function __construct(QMUserVariableRelationship $c){
        $this->analyzable = $this->userVariableRelationship = $c;
        $causeEffect = $c->getCauseAndEffectString();
        $problemDetails = "Not Enough Overlapping $causeEffect Data";
        $problemDetails.= "\n<br>".$c->getCauseUserVariable()->getUrl();
        $problemDetails.= "\n<br>".$c->getEffectUserVariable()->getUrl();
        $c->addException($this);
        parent::__construct($c, $problemDetails, $problemDetails);
    }
}
