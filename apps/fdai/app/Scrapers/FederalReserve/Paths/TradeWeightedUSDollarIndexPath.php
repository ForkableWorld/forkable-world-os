<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve\Paths;
use App\Scrapers\FederalReserve\ObservationsPath;
class TradeWeightedUSDollarIndexPath extends ObservationsPath
{
    const DESCRIPTION = "The trade-weighted US dollar index, also known as the broad index, is a measure of the value of the United States dollar relative to other world currencies. It is a trade weighted index that improves on the older U.S. Dollar Index by using more currencies and the updating the weights yearly (rather than never). The base index value is 100 in Jan 1997.";
    public function getSeriesId(): string{
        return "DTWEXAFEGS";
    }
    public function variableName(): string{
        return "Trade Weighted U.S. Dollar Index: Advanced Foreign Economies, Goods and Services";
    }
    public function getSubtitleAttribute(): string{
        return self::DESCRIPTION;
    }
}
