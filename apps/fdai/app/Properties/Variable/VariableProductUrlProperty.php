<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseProductUrlProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\Types\QMStr;

class VariableProductUrlProperty extends BaseProductUrlProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;

    /**
     * @param $providedParams
     * @return string
     */
    public static function getProductUrlFromNewVariableParams(array $providedParams): ?string
    {
        $url = QMArr::getValue($providedParams, [
            'productUrl',
            'DetailPageURL'
        ]);
        if ($url) {
            QMStr::assertIsUrl($url, "ProductUrl");
        }
        return $url;
    }
}
