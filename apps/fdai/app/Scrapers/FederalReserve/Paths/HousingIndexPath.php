<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve\Paths;
use App\Scrapers\FederalReserve\ObservationsPath;
use App\Units\IndexUnit;
class HousingIndexPath extends ObservationsPath
{
    public function getSeriesId(): string{
        /** @noinspection SpellCheckingInspection */
        return "CSUSHPISA";
    }
    public function variableName(): string{
        return "Case-Shiller U.S. National Home Price Index";
    }
    public function unitId(): int{
        return IndexUnit::ID;
    }
}
