<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Cards\QMCard;
class CardNotification extends BaseNotification
{
    private $card;
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(QMCard $card){
        $this->card = $card;
    }
    public function getTitleAttribute(): string{
        return $this->getCard()->getTitleAttribute();
    }
    public function getBody(): string{
        return $this->getCard()->getContent();
    }
    public function getIcon(): string{
        return $this->getCard()->getImage();
    }
    public function getUrl(array $params = []): string{
        return $this->getCard()->getUrl();
    }
    /**
     * @return QMCard
     */
    public function getCard(): QMCard {
        return $this->card;
    }
}
