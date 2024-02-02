<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\Models\GlobalVariableRelationship;
use App\Slim\View\Request\QMRequest;
use Illuminate\Support\Collection;
class AggregatedCorrelationListExplanationResponseBody extends CorrelationListExplanationResponseBody {
    public const TITLE = "New Discoveries";
    /**
     * AggregatedCorrelationListExplanation constructor.
     * @param $filters
     * @param GlobalVariableRelationship[]|Collection $correlations
     */
    public function __construct($filters, $correlations){
        parent::__construct($correlations);
        $this->setTitle("New Discoveries", $filters);
        $this->setDescription("Here are the top new discoveries for the average user.", $filters, " for the average user.");
        $this->setIonIcon("ion-ios-people");
        $this->setStartTracking([
            'title'       => 'Personalized Results',
            'description' => "Want to see what hidden factors could be influencing YOUR health and happiness?  Start tracking!",
            'button'      => [
                'text' => 'Start Tracking',
                'link' => QMRequest::host()
            ]
        ]);
    }
}
