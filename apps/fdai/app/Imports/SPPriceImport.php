<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Imports;
class SPPriceImport extends SchillerImport
{
    public function value(array $row): float{
        return $row[1];
    }
    public function variableName(array $row): string{
        return "Real S&P Composite Stock Price Index";
    }
}
