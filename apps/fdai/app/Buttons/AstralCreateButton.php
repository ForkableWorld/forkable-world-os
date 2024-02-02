<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\BaseModel;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
class AstralCreateButton extends AstralIndexButton
{
    /**
     * AstralButton constructor.
     * @param BaseModel|string $class
     * @param string|null $label
     */
    public function __construct(string $class, string $label = null){
        parent::__construct($class, $label ?? "Create ".$class::getClassNameTitle());
        $this->setTooltip($class::getClassDescription());
        $this->setFontAwesome($class::getClassFontAwesome());
        $this->setUrl( $class::getAstralCreateUrl());
    }
    public function getAstralMenuItem(): AbstractResource {
        return parent::getAstralMenuItem()->create();
    }
}
