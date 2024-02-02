<?php
namespace App\Properties\Phrase;
use App\Models\Phrase;
use App\Traits\PropertyTraits\PhraseProperty;
use App\Properties\Base\BaseNumberOfTimesHeardProperty;
class PhraseNumberOfTimesHeardProperty extends BaseNumberOfTimesHeardProperty
{
    use PhraseProperty;
    public $table = Phrase::TABLE;
    public $parentClass = Phrase::class;
}