<?php
namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasWpUsermetum;
use App\Models\WpUsermetum;
trait WpUsermetumProperty
{
    use HasWpUsermetum;
    public function getWpUsermetumId(): int{
        return $this->getParentModel()->getId();
    }
    public function getWpUsermetum(): WpUsermetum{
        return $this->getParentModel();
    }
}