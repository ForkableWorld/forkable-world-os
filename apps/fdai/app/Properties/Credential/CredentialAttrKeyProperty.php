<?php
namespace App\Properties\Credential;
use App\Models\Credential;
use App\Traits\PropertyTraits\CredentialProperty;
use App\Properties\Base\BaseAttrKeyProperty;
class CredentialAttrKeyProperty extends BaseAttrKeyProperty
{
    use CredentialProperty;
    public $table = Credential::TABLE;
    public $parentClass = Credential::class;
}