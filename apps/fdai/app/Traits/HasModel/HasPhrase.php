<?php
namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\Phrase;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasPhrase
{
    public function getPhraseId(): int {
        $nameOrId = $this->getAttribute('phrase_id');
        return $nameOrId;
    }
    public function getPhraseButton(): QMButton {
        $phrase = $this->getPhrase();
        if($phrase){
            return $phrase->getButton();
        }
        return Phrase::generateShowButton($this->getPhraseId());
    }
    /**
     * @return Phrase
     */
    public function getPhrase(): Phrase {
        if($this instanceof BaseProperty && $this->parentModel instanceof Phrase){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('phrase')){return $l;}
        $id = $this->getPhraseId();
        $phrase = Phrase::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['phrase'] = $phrase; }
        if(property_exists($this, 'phrase')){
            $this->phrase = $phrase;
        }
        return $phrase;
    }
}