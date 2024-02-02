<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Parameters;
use App\Models\BaseModel;
use OpenApi\Annotations\Parameter;
class QMParameter extends Parameter
{
    public $in = "query";
    public function __construct(string $name = null, array $schema = null, BaseModel $model = null){
        parent::__construct([]);
        if($name){$this->name = $name;}
        if($schema){
            if(isset($schema['type']) && $schema['type'] === "number"){
                unset($schema['format']); // Maybe float? https://swagger.io/docs/specification/data-models/data-types/
            }
            $this->schema = $schema;
            $this->type = $schema['type'];
        }
        if($model){
            $this->setParameterKey($model);
        }
    }
    public function setParameterKey(BaseModel $model){
        $this->parameter = $model->getTable().'_'.$this->name;
    }
}
