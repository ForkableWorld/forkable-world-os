<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\VariableRelationships\CorrelationListExplanationResponseBody;
class CohortStudyListResponseBody extends StudyListResponseBody {
    public $studies;
    /**
     * StudiesAndExplanationResponseAndExplanationResponse constructor.
     * @param CorrelationListExplanationResponseBody $correlationResponse
     */
    public function __construct($correlationResponse = null){
        parent::__construct($correlationResponse);
        $this->summary = "Cohort Studies";
        $this->description = "";
    }
    /**
     * @param QMPopulationStudy[]|QMUserStudy[] $studies
     */
    public function setStudies($studies){
        $this->studies = $studies;
    }
}
