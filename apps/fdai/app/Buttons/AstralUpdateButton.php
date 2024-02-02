<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\BaseModel;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
class AstralUpdateButton extends AstralButton
{
    /**
     * @var BaseModel
     */
    protected $model;
    /**
     * AstralButton constructor.
     * @param BaseModel $model
     * @param string|null $label
     */
    public function __construct(BaseModel $model, string $label = null){
        $this->model = $model;
        parent::__construct($label ?? "Edit " . $model->getTitleAttribute());
        $this->setTooltip($model->getSubtitleAttribute());
        $this->setFontAwesome($model->getFontAwesome());
        $this->setUrl($model->getAstralUpdateUrl());
        $this->setImage($model->getImage());
    }
    public function getAstralMenuItem(): AbstractResource {
        return parent::getAstralMenuItem()->update($this->getModel()->getId());
    }
    /**
     * @return BaseModel
     */
    public function getModel(): BaseModel{
        return $this->model;
    }
}
