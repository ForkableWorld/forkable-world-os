<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
class Feature {
    public $title;
    public $subTitle;
    public $image;
    public $premium;
    /**
     * Feature constructor.
     * @param $object
     */
    public function __construct($object){
        foreach($object as $key => $value){
            $this->$key = $value;
        }
    }
}
