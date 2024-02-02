<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Parameters;
use App\Models\BaseModel;
class IdPathParameter extends IntParameter
{
    public $name = 'id';
    public $in = 'path';
    public $required = true;
    public function __construct(BaseModel $model){
        parent::__construct(null, null,$model);
    }
}
