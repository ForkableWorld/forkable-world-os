<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\SpreadsheetImporters;
use App\DataSources\QMSpreadsheetImporter;
use App\Units\DollarsUnit;
use App\VariableCategories\PaymentsVariableCategory;
class MintSpreadsheetImporter extends QMSpreadsheetImporter {
	public const AFFILIATE = false;
	public const BACKGROUND_COLOR = '#4cd964';
	public const CLIENT_REQUIRES_SECRET = false;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = DollarsUnit::ABBREVIATED_NAME;
	public const DEFAULT_VARIABLE_CATEGORY_NAME = PaymentsVariableCategory::NAME;
	public const DISPLAY_NAME = 'Mint Spreadsheet Upload';
	public const ENABLED = 1;
	public const GET_IT_URL = null;
	public const ID = 73;
	public const IMAGE = 'https://static-s.aa-cdn.net/img/gp/20600000039023/Vd9ubTf3GHgogznhGqYLb-hFtx6gqhZ5h5wBzSf-1Wf_GsFHRf1Lk_HX0muiCTp1fL_u=w300?v=1';
	public const LOGO_COLOR = '#2d2d2d';
	public const LONG_DESCRIPTION = 'Upload an exported transactions spreadsheet from Mint.com. Manage your money, pay your bills and track your credit score with Mint. Now that\'s being good with your money. ';
	public const NAME = 'mint-spreadsheet';
	public const PREMIUM = false;
	public const SHORT_DESCRIPTION = 'Tracks expenditures';
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
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $spreadsheetUpload = self::SPREADSHEET_UPLOAD;
    public function getAllowedUnitIds(): array{
        return [
            DollarsUnit::ID,
        ];
    }
}
