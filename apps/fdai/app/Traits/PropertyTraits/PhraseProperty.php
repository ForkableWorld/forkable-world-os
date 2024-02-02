<?php
namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasPhrase;
use App\Models\Phrase;
trait PhraseProperty
{
    use HasPhrase;
    public function getPhraseId(): int{
        return $this->getParentModel()->getId();
    }
    public function getPhrase(): Phrase{
        return $this->getParentModel();
    }
}