<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\UI\IonIcon;
use App\Utils\UrlHelper;
class UserVariableRelationshipListExplanationResponseBody extends CorrelationListExplanationResponseBody {
    public const TITLE = "Discoveries from Your Data";
    /**
     * UserVariableRelationshipListExplanationResponseBody constructor.
     * @param $filters
     * @param $correlations
     */
    public function __construct($filters, $correlations = []){
        parent::__construct($correlations);
        $this->setTitle(self::TITLE, $filters);
        $this->setDescription("When I was eating your data, I made some interesting discoveries!", $filters, " based on your own data.");
        $this->setIonIcon(IonIcon::ion_icon_person);
    }
    /**
     * @param array $filters
     * @return mixed
     */
    public static function getUserVariableRelationshipsExplanationArray($filters = []){
        return json_decode(json_encode(new self($filters)), true);
    }
}
