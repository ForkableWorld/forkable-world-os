<?php
namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasCredential;
use App\Models\Credential;
trait CredentialProperty
{
    use HasCredential;
    public function getCredentialId(): int{
        return $this->getParentModel()->getId();
    }
    public function getCredential(): Credential{
        return $this->getParentModel();
    }
}