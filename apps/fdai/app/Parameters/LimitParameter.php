<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Parameters;
use App\Models\BaseModel;
class LimitParameter extends IntParameter
{
    public $name = 'limit';
    public function __construct(BaseModel $model){
        parent::__construct(null, null, $model);
    }
}
