<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve\Paths;
use App\Scrapers\FederalReserve\ObservationsPath;
class EconomicPolicyUncertaintyIndexPath extends ObservationsPath
{
    public function getSeriesId(): string{
        return "USEPUINDXD";
    }
    public function variableName(): string{
        return "Economic Policy Uncertainty Index for United States";
    }
}
