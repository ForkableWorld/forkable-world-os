<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseSubscriptionProviderProperty extends EnumProperty {
	public $dbInput = 'string:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'subscription_provider';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::SUBSCRIPTION_PROVIDER;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::SUBSCRIPTION_PROVIDER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'subscription_provider';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable';
	public $title = 'Subscription Provider';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable';

    const GOOGLE = 'google';
    const APPLE = 'apple';
    const STRIPE = 'stripe';

    protected function isLowerCase(): bool
    {
        return true;
    }

    public function getEnumOptions(): array
    {
        return $this->enum = [
            self::GOOGLE,
            self::APPLE,
            self::STRIPE,
        ];
    }
}
