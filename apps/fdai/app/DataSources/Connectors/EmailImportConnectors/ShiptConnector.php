<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\EmailImportConnectors;
use App\DataSources\EmailImportConnector;
use App\Exceptions\InvalidVariableNameException;
use App\VariableCategories\FoodsVariableCategory;
class ShiptConnector extends EmailImportConnector {
    protected const DEVELOPER_CONSOLE = null;
    
    
    
    
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#23448b';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Payments';
	public const DISPLAY_NAME = 'Shipt';
	protected const ENABLED = false;
	protected const GET_IT_URL = 'https://shipt.com';
	public const ID = 77;
	public const IMAGE = 'https://upload.wikimedia.org/wikipedia/commons/4/4e/Gmail_Icon.png';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Automatically see how supplements and foods might be improving or exacerbating your symptom severity by connecting GMail and importing your email receipts from Amazon, Instacart, etc.';
	public const NAME = 'shipt';
	protected const SHORT_DESCRIPTION = 'Automate your tracking by importing your email receipts from Amazon, Instacart, etc.';
    public const TEST_VARIABLE = 'Maldon Sea Salt Flakes';
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
	public $shortDescription = self::SHORT_DESCRIPTION;
    // Test User: quantimodo.test.user@gmail.com
    // Test PW: Iamapassword1!
    protected const CONNECTOR_ID = 77;
    /**
     * @return int
     * @throws InvalidVariableNameException
     */
    public function importData(): void {
        $this->getMessagesAndSaveMeasurements('support@shipt.com', FoodsVariableCategory::NAME);

    }
}
