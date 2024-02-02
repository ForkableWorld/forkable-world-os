<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\BaseModel;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
class AstralDetailsButton extends AstralUpdateButton
{
	/**
	 * AstralDetailsButton constructor.
	 * @param BaseModel $model
	 * @param string|null $label
	 * @param array|null $params
	 */
    public function __construct(BaseModel $model, string $label = null, array $params = null){
        parent::__construct($model, $label ?? "View ".$model->getTitleAttribute());
        $this->setUrl($model->getAstralShowUrl($params));
    }
	/**
	 * @return \DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource
	 */
	public function getAstralMenuItem(): AbstractResource {
        return parent::getAstralMenuItem()
            ->detail($this->getModel()->getId());
    }
}
