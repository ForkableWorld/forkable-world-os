<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\UserVariable;
use App\Types\QMArr;
class UserVariableButton extends QMButton
{
    public function __construct(UserVariable $v){
        parent::__construct($v->getTitleAttribute(), $v->getUrl(), $v->getColor(), null);
        $this->setImage($v->getImage());
        $this->fontAwesome = $v->getFontAwesome();
        $this->badgeText = $v->number_of_measurements;
        $this->tooltip = $this->badgeText." studies on the causes or effects of ".$v->number_of_measurements;
    }
    /**
     * @param UserVariable[] $variables
     * @return array
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function toButtons($variables): array{
        $buttons = [];
        foreach($variables as $v){
            $b = new static($v);
            $buttons[$v->getVariableName()] = $b;
        }
        QMArr::sortDescending($buttons, 'badgeText');
        return $buttons;
    }
}
