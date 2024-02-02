<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BasePostNameProperty;
use LogicException;
class WpPostPostNameProperty extends BasePostNameProperty
{
    use WpPostProperty;
    public $table = WpPost::TABLE;
    public $parentClass = WpPost::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $post = $this->getWpPost();
        $value = $this->getDBValue();
        if($post->post_title === $value){
            le("Post Name should not equal post name but is ".$value ." in ".
                \App\Logging\QMLog::print_r($post->toArray(), true));
        }
    }
}
