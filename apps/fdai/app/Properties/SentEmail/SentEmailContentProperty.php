<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\SentEmail;
use App\Models\SentEmail;
use App\Traits\PropertyTraits\IsHtml;
use App\Traits\PropertyTraits\SentEmailProperty;
use App\Properties\Base\BaseContentProperty;
use App\Types\QMStr;
use App\Fields\Text;
class SentEmailContentProperty extends BaseContentProperty
{
    use SentEmailProperty, IsHtml;
    public $table = SentEmail::TABLE;
    public $parentClass = SentEmail::class;
    protected function getHtmlField($displayCallback, string $title = null, $callback = null): Text{
        return Text::make($title ?? $this->getTitleAttribute(), $this->name, function($value, $resource, $attribute){
            $html = QMStr::after("</noscript>", $value, $value);
            return $html;
        })
            ->hideFromIndex()
            ->asHtml();
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
