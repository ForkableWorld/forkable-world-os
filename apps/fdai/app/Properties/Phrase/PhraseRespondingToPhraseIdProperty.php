<?php
namespace App\Properties\Phrase;
use App\Models\Phrase;
use App\Traits\PropertyTraits\PhraseProperty;
use App\Properties\Base\BaseRespondingToPhraseIdProperty;
class PhraseRespondingToPhraseIdProperty extends BaseRespondingToPhraseIdProperty
{
    use PhraseProperty;
    public $table = Phrase::TABLE;
    public $parentClass = Phrase::class;
}