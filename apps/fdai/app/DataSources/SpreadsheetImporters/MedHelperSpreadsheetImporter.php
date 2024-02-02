<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\SpreadsheetImporters;
use App\DataSources\QMSpreadsheetImporter;
use App\Models\UnitCategory;
use App\Storage\S3\S3Images;
use App\UnitCategories\WeightUnitCategory;
use App\Units\CountUnit;
use App\Units\MilligramsUnit;
use App\Units\PillsUnit;
use App\Units\PuffsUnit;
use App\Units\TabletsUnit;
class MedHelperSpreadsheetImporter extends QMSpreadsheetImporter {
	public const AFFILIATE = false;
	public const BACKGROUND_COLOR = '#00afd8';
	public const CLIENT_REQUIRES_SECRET = false;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = 'Milligrams';
	public const DEFAULT_VARIABLE_CATEGORY_NAME = 'Treatments';
	public const DISPLAY_NAME = 'MedHelper';
	public const ENABLED = 1;
	public const GET_IT_URL = null;
	public const ID = 69;
	public const IMAGE = S3Images::S3_IMAGE_URL."/connectors/medhelper.png";
	public const LOGO_COLOR = '#2d2d2d';
	public const LONG_DESCRIPTION = 'MedHelper is a comprehensive prescription/medication compliance and tracking App designed to help individuals and caretakers manage the challenges of staying on time up to date and on schedule with very simple to very complex regimes. Easy to install and full featured MedHelper is ready to become your 24/7 healthcare assistant. Available on Android and IOS platforms.';
	public const NAME = 'medhelper';
	public const SHORT_DESCRIPTION = 'Tracks medications.';
	public const SPREADSHEET_UPLOAD = true;
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultUnitAbbreviatedName = self::DEFAULT_UNIT_ABBREVIATED_NAME;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $spreadsheetUpload = self::SPREADSHEET_UPLOAD;
	public function getAllowedUnitIds(): array{
        $allowed = [
            CountUnit::ID,
            TabletsUnit::ID,
            MilligramsUnit::ID,
            PuffsUnit::ID,
            PillsUnit::ID,
        ];
        $weight = UnitCategory::findInMemoryOrDB(WeightUnitCategory::ID);
        $weightUnits = $weight->getUnits();
        $ids = collect($weightUnits)->pluck('id')->all();
        $allowed = array_merge($ids, $allowed);
        return $allowed;
    }
}
