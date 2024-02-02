<?php
namespace App\Properties\Credential;
use App\Models\Credential;
use App\Traits\PropertyTraits\CredentialProperty;
use App\Properties\Base\BaseStatusProperty;
class CredentialStatusProperty extends BaseStatusProperty
{
    use CredentialProperty;
    public $table = Credential::TABLE;
    public $parentClass = Credential::class;

    protected function isLowerCase(): bool
    {
        return true;
    }

    public function getEnumOptions(): array
    {
        return [];
    }
}
