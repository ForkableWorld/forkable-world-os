<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Application;
use App\Models\Application;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\ApplicationProperty;
use App\Traits\PropertyTraits\IsString;

class ApplicationStateProperty extends BaseProperty
{
    use ApplicationProperty;
    use IsString;
    const NAME = 'state';
    const LABEL = 'State';
    const DESCRIPTION = 'The US state of the company that owns the application';
    const EXAMPLE = 'draft';
    const REQUIRED = true;
    const DEFAULT = 'IL';
    const IS_ARRAY = false;
    public $name = self::NAME;
    public $table = Application::TABLE;
    public $parentClass = Application::class;
	protected function isLowerCase(): bool{
		return true;
	}
	public function getEnumOptions(): array{
		return $this->enum;
	}
}
