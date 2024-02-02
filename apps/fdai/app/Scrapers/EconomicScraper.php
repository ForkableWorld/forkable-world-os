<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers;
use App\Properties\Base\BaseUserLoginProperty;
abstract class EconomicScraper extends BaseScraper
{
    public function getUserLogin(): string {
        return BaseUserLoginProperty::USER_LOGIN_ECONOMIC_DATA;
    }
}
