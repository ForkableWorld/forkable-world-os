<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\Models\GlobalVariableRelationship;
use App\Types\QMArr;
use Illuminate\Support\Collection;
use App\Slim\Model\QMResponseBody;
class CorrelationsAndExplanationResponseBody extends QMResponseBody {
    public $correlations;
    public $explanation;
    public $studies;
    /**
     * CorrelationsAndExplanationResponseBody constructor.
     * @param GlobalVariableRelationship[]|QMVariableRelationship[]|QMGlobalVariableRelationship[]|Collection $correlations
     * @param $requestParams
     */
    public function __construct($correlations, array $requestParams){
        parent::__construct();
        $this->correlations = $correlations;
        $this->explanation = new AggregatedCorrelationListExplanationResponseBody($requestParams, $correlations);
        if(isset($correlations[0], $correlations[0]->userId) && $correlations){
            $this->explanation = new UserVariableRelationshipListExplanationResponseBody($requestParams, $correlations);
        }
    }
    /**
     * @return string
     */
    public function getHtml(): string {
        return $this->getExplanation()->getHtml();
    }
    /**
     * @return CorrelationListExplanationResponseBody
     */
    public function getExplanation(): CorrelationListExplanationResponseBody{
        return $this->explanation;
    }
    public static function get(array $filters): CorrelationsAndExplanationResponseBody {
        if($filters['aggregate'] ?? false){
            $correlations = QMGlobalVariableRelationship::getGlobalVariableRelationships($filters);
        } else {
            $correlations = QMUserVariableRelationship::getUserVariableRelationships($filters);
        }
        return new CorrelationsAndExplanationResponseBody($correlations, $filters);
    }
    /**
     * @return GlobalVariableRelationship[]|Collection|QMGlobalVariableRelationship[]|QMVariableRelationship[]
     */
    public function getCorrelations(): array {
        return QMArr::toArray($this->correlations);
    }
}
