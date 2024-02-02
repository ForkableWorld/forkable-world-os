<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseImageUrlProperty;
use App\Utils\AppMode;
use App\Types\QMArr;
use LogicException;
use App\Variables\QMCommonVariable;
use Throwable;

class VariableImageUrlProperty extends BaseImageUrlProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param array $arr
     * @return string|null
     */
    public static function getImageUrlForNewVariableArray(array $arr): ?string
    {
        $url = QMArr::getValue($arr, [
            self::NAME,
            'imageUrl'
        ]);
        if (!$url) {
            $url = $arr['MediumImage']['URL'] ?? $arr['icon'] ?? null;
        }
        if ($url) {
            try {
                BaseImageUrlProperty::assertIsImageUrl($url, "imageUrl");
            } catch (Throwable $e) {
                if (!AppMode::isApiRequest()) {
                    /** @var LogicException $e */
                    throw $e;
                }
                QMLog::error("$url is not a valid image url");
                return null;
            }
        }
        return $url;
    }
}
