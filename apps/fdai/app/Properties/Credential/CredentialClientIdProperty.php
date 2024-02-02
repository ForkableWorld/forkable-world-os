<?php
namespace App\Properties\Credential;
use App\Models\Credential;
use App\Traits\PropertyTraits\CredentialProperty;
use App\Properties\Base\BaseClientIdProperty;
class CredentialClientIdProperty extends BaseClientIdProperty
{
    use CredentialProperty;
    public $table = Credential::TABLE;
    public $parentClass = Credential::class;
}