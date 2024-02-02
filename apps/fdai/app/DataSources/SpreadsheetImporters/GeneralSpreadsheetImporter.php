<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\SpreadsheetImporters;
use App\UI\ImageUrls;
use App\DataSources\QMSpreadsheetImporter;
class GeneralSpreadsheetImporter extends QMSpreadsheetImporter {
	public const AFFILIATE = false;
	public const BACKGROUND_COLOR = '#23448b';
	public const CLIENT_REQUIRES_SECRET = false;
	public const DEFAULT_VARIABLE_CATEGORY_NAME = null;
	public const DISPLAY_NAME = 'General Spreadsheet';
	public const ENABLED = 1;
	public const GET_IT_URL = null;
	public const ID = 75;
	public const IMAGE = ImageUrls::GOOGLE_SHEETS;
	public const LOGO_COLOR = '#2d2d2d';
	public const LONG_DESCRIPTION = 'Import from a spreadsheet containing a Variable Name, Value, Measurement Event Time, and Abbreviated Unit Name field.  Here is an <a href="http://bit.ly/2jz7CNl" target="_blank">example spreadsheet</a> with allowed column names, units and time format.';
	public const NAME = 'general_spreadsheet';
	public const PREMIUM = false;
	public const SHORT_DESCRIPTION = 'Import from a spreadsheet containing a Variable Name, Value, Measurement Event Time, and Abbreviated Unit Name field';
	public const SPREADSHEET_UPLOAD = true;
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $spreadsheetUpload = self::SPREADSHEET_UPLOAD;
}
