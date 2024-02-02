<?php
namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasIpDatum;
use App\Models\IpDatum;
trait IpDatumProperty
{
    use HasIpDatum;
    public function getIpDatumId(): int{
        return $this->getParentModel()->getId();
    }
    public function getIpDatum(): IpDatum{
        return $this->getParentModel();
    }
}