<?php
namespace App\Properties\Phrase;
use App\Models\Phrase;
use App\Traits\PropertyTraits\PhraseProperty;
use App\Properties\Base\BaseResponsePhraseIdProperty;
class PhraseResponsePhraseIdProperty extends BaseResponsePhraseIdProperty
{
    use PhraseProperty;
    public $table = Phrase::TABLE;
    public $parentClass = Phrase::class;
}