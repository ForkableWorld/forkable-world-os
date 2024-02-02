<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\Slim\Controller\Connector\ConnectorException;
use App\UI\ImageUrls;
use Google_Service_Sheets;
use App\DataSources\GoogleBaseConnector;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
class GoogleSheetsConnector extends GoogleBaseConnector {
	protected const BACKGROUND_COLOR = '#2c6efc';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Activities';
	public const DISPLAY_NAME = 'Google Sheets';
	protected const ENABLED = false; // TODO: Re-enable after adding Experimental warning on front end
	protected const GET_IT_URL = 'https://calendar.google.com';
	protected const LOGO_COLOR = '#d34836';
	protected const SHORT_DESCRIPTION = 'Connect Google Sheets to automatically import your data.';
	protected const LONG_DESCRIPTION = GeneralSpreadsheetImporter::LONG_DESCRIPTION;
    public $backgroundColor = self::BACKGROUND_COLOR;
    public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
    public $displayName = self::DISPLAY_NAME;
    public $enabled = self::ENABLED;
    public $getItUrl = self::GET_IT_URL;
    public $id = self::ID;
    public $image = self::IMAGE;
    public $logoColor = self::LOGO_COLOR;
    public $longDescription = self::LONG_DESCRIPTION;
    public $name = self::NAME;
    public $providesUserProfileForLogin = false;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 96;
    public const IMAGE = ImageUrls::GOOGLE_SHEETS;
    public const NAME = 'google-sheets';
    public static array $SCOPES = [
        Google_Service_Sheets::SPREADSHEETS,
        Google_Service_Sheets::SPREADSHEETS_READONLY,
    ];
	/**
	 * @return ConnectorException|int|void
	 */
	public function importData(): void {
        // TODO
    }
}
