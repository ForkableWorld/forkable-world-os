<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\PasswordConnector;
use App\Exceptions\NoChangesException;
use App\Exceptions\TemporaryConnectionException;
use App\Products\AmazonHelper;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\CommonVariables\NutrientsCommonVariables\CarbsCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\FatIntakeCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\ProteinCommonVariable;
use Closure;
/** Class MyFitnessPalConnector
 * @package App\DataSources\Connectors
 */
class MyFitnessPalConnector extends PasswordConnector {
    protected const DEVELOPER_CONSOLE = null;
    
    
    
    
	private $apiUrl = 'http://www.myfitnesspal.com/reports/results/nutrition/';
	private $endpoints;
	private static $EXTRACT_TOKEN_PATTERN = '/authenticity_token\" type=\"hidden\" value=\"(.+)\"/';
	private static $LOGIN_INVALID_USER_PASS_MESSAGE = 'Incorrect username or password';
	private static $LOGIN_TOO_MANY_FAILED_MESSAGE = 'exceeded the maximum number of consecutive failed login attempts';
	private static $URL_LOGIN = 'https://www.myfitnesspal.com/account/login';
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#262626';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Foods';
	public const DISPLAY_NAME = 'MyFitnessPal';
	protected const ENABLED = 1;
    protected const GET_IT_URL = 'http://www.amazon.com/gp/product/B004H6WTJI/ref=as_li_qf_sp_asin_il?ie=UTF8&camp=1789&creative=9325&creativeASIN=B004H6WTJI&linkCode=as2';
	public const IMAGE = 'https://i.imgur.com/2aUrwtd.png';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'Lose weight with MyFitnessPal, the fastest and easiest-to-use calorie counter for iPhone and iPad. With the largest food database of any iOS calorie counter (over 3,000,000 foods), and amazingly fast food and exercise entry.';
	protected const SHORT_DESCRIPTION = 'Tracks diet.';
    public static $BASE_API_URL = 'http://www.myfitnesspal.com/';
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
    public const DISABLED_UNTIL = "2023-04-01";
    public const ID = 1;
    public const NAME = 'myfitnesspal';
    //sources: "MyFitnessPal"
    //Category: "Nutrients" => name: "Calcium", "Carbs" , "Cholesterol", "Fiber", "Trans Fat", "Saturated Fat", "Iron",
    //"Monounsaturated Fat", "Potassium", "Polyunsaturated Fat", "Protein", "Vitamin A", "Sodium"
    //Category: "Physique" => name: "Fat"
    //Category: "Foods" => name: "Sugar"
    //Category: "Treatments" => name: "Vitamin C"
	/**
	 * @throws ConnectException
	 * @throws ConnectorException
	 * @throws NoChangesException
	 */
    public function importData(): void {
	    $this->login();
        $fromTime = $this->getFromTime();
        $this->setEndpoints();
        $timeDiffSeconds = time() - $fromTime;
        $timeDiffDays = ceil(($timeDiffSeconds / (60 * 60 * 24)) - 0.5);    // -0.5 to allow two syncs per day
        // Limit to one years of data
        if($timeDiffDays > 364){$timeDiffDays = 364;}
        foreach($this->endpoints as $this->currentUrl => $createMeasurementSetFunction){
            $response = $this->fetchArray($this->currentUrl, [], ['_DAYS_' => $timeDiffDays]);
            $this->parseResponseAndSaveMeasurements($response, $createMeasurementSetFunction);
        }
    }
	/**
	 * Parses measurements received from MyFitnessPal
	 * $createMeasurementSetFunction should take an associative array of measurements
	 * and convert it into a measurement
	 * @param array $arr
	 * @param Closure $createMeasurementSetFunction
	 * @return void
	 * @throws NoChangesException
	 */
    private function parseResponseAndSaveMeasurements(array $arr, Closure $createMeasurementSetFunction): void{
        $jsonData = $arr['data'];
        // MyFitnessPal doesn't return a year, so keep track of the month so that
        // we can decrease currentMeasurementMonth
        // when going from month #1 to month #12 in the previous year
        $previousMeasurementMonth = (int)date('m');
        $currentMeasurementYear = (int)date('Y');
        $measurements = [];
        $numRawMeasurements = count($arr['data']);
        // Loop in reverse order so that we can track the year
        $zeroMeasurements = 0;
        for($i = $numRawMeasurements - 1; $i >= 0; $i--){
            $myFitnessPalMeasurement = $arr['data'][$i];
            //$this->logDebug('MyFitnessPal parsing ', $myFitnessPalMeasurement);
            // Filter out 0 values, we can't differentiate between "didn't track" or "didn't take", so we don't store them
            // as measurements. The API can figure out what to do about it.
            if($myFitnessPalMeasurement['total'] == 0){
                $zeroMeasurements++;
                $this->logDebug("Filtering out 0 value because we can't differentiate between \"didn't track\" or \"didn't take\"");
                continue;
            }
            $dateComponents = explode('/', $myFitnessPalMeasurement['date']);
            $currentMeasurementMonth = (int)$dateComponents[0];
            $currentMeasurementDay = (int)$dateComponents[1];
            // Happens when we went from month 1 to month 12. Breaks if you skip an entire year worth of data,
            // but that'll be very rare
            if($currentMeasurementMonth > $previousMeasurementMonth){
                $currentMeasurementYear--;
            }
            $timestamp = mktime(0, 0, 0, $currentMeasurementMonth, $currentMeasurementDay, $currentMeasurementYear);
            if($timestamp > time() + 86400){
                $timestamp = mktime(0, 0, 0, $currentMeasurementMonth, $currentMeasurementDay, $currentMeasurementYear - 1);
            }
            $measurements[] = new QMMeasurement($timestamp, $myFitnessPalMeasurement['total']);
            $previousMeasurementMonth = $currentMeasurementMonth;
        }
        if($zeroMeasurements === count($jsonData)){
            $this->logInfo("All measurements were zero");
	        return;
        }
        $measurementSet = $createMeasurementSetFunction($measurements);
        if(!isset($measurementSet->measurementItems)){
            le("measurementSet->measurements is not defined!");
        }
        $this->logDebug('Got MyFitnessPal measurements ', ['Number of Measurements' => count($measurementSet->measurementItems)]);
        if(!empty($measurementSet)){
            $this->saveMeasurementSets([$measurementSet]);
        }
    }
    public function setEndpoints(): void{
        $this->endpoints = [
            $this->apiUrl.'Carbs/_DAYS_.json?report_name=1'               => function($measurements){
                return new MeasurementSet(CarbsCommonVariable::NAME,
                    $measurements,
                    'g',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Fat/_DAYS_.json?report_name=1'                 => function($measurements){
                return new MeasurementSet(FatIntakeCommonVariable::NAME,
                    $measurements,
                    'g',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Protein/_DAYS_.json?report_name=1'             => function($measurements){
                return new MeasurementSet(ProteinCommonVariable::NAME,
                    $measurements,
                    'g',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Saturated Fat/_DAYS_.json?report_name=1'       => function($measurements){
                return new MeasurementSet('Saturated Fat', $measurements, 'g', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Polyunsaturated Fat/_DAYS_.json?report_name=1' => function($measurements){
                return new MeasurementSet('Polyunsaturated Fat',
                    $measurements,
                    'g',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Monounsaturated Fat/_DAYS_.json?report_name=1' => function($measurements){
                return new MeasurementSet('Monounsaturated Fat',
                    $measurements,
                    'g',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Trans Fat/_DAYS_.json?report_name=1'           => function($measurements){
                return new MeasurementSet('Trans Fat', $measurements, 'g', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Cholesterol/_DAYS_.json?report_name=1'         => function($measurements){
                return new MeasurementSet('Cholesterol', $measurements, 'mg', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Sodium/_DAYS_.json?report_name=1'              => function($measurements){
                return new MeasurementSet('Sodium', $measurements, 'mg', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Potassium/_DAYS_.json?report_name=1'           => function($measurements){
                return new MeasurementSet('Potassium', $measurements, 'mg', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Fiber/_DAYS_.json?report_name=1'               => function($measurements){
                return new MeasurementSet('Fiber', $measurements, 'g', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Sugar/_DAYS_.json?report_name=1'               => function($measurements){
                return new MeasurementSet('Sugar (g)', $measurements, 'g', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Vitamin A/_DAYS_.json?report_name=1'           => function($measurements){
                return new MeasurementSet('Vitamin A', $measurements, '%RDA', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Vitamin C/_DAYS_.json?report_name=1'           => function($measurements){
                return new MeasurementSet('Vitamin C (%RDA)',
                    $measurements,
                    '%RDA',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            },
            $this->apiUrl.'Iron/_DAYS_.json?report_name=1'                => function($measurements){
                return new MeasurementSet('Iron', $measurements, '%RDA', 'Nutrients', $this->displayName, 'SUM');
            },
            $this->apiUrl.'Calcium/_DAYS_.json?report_name=1'             => function($measurements){
                return new MeasurementSet('Calcium (%RDA)',
                    $measurements,
                    '%RDA',
                    'Nutrients',
                    $this->displayName,
                    'SUM');
            }
        ];
    }
    public function makeSerializable(){
        parent::makeSerializable();
        $this->endpoints = null;
    }
	/**
	 * @throws ConnectException
	 * @throws ConnectorException
	 */
	protected function login(){
		// Send our initial request to the home page to get a special token required for login
		$html = $this->fetchHtml("/");
		// Get authenticity token (their protection against CSRF).
		preg_match(self::$EXTRACT_TOKEN_PATTERN, $html, $matches);
		if(!isset($matches[1])){
			$this->logError("Could not get authenticityToken from response: " . $html);
			throw new TemporaryConnectionException($this->getTitleAttribute());
		}
		$authenticityToken = $matches[1];
		// Create an array of POST parameters
		$loginParameters = [
			'utf8' => '✓',
			'authenticity_token' => $authenticityToken,
			'username' => $this->getConnectorUserName(),
			'password' => $this->getConnectorPassword(),
			'remember_me' => 1,
		];
		// Do the POST request to log in the user
		$response = $this->post(self::$URL_LOGIN, null, $loginParameters);
		$responseBody = $response->getOriginalContent();
		// If the response contains one of our error messages the login was unsuccessful
		if(strpos($responseBody, self::$LOGIN_INVALID_USER_PASS_MESSAGE) !== false){
			$m = 'Invalid username or password';
			$this->handleFailedLogin($m);
		}
		if(strpos($responseBody, self::$LOGIN_TOO_MANY_FAILED_MESSAGE) !== false){
			$this->handleFailedLogin(self::$LOGIN_TOO_MANY_FAILED_MESSAGE);
			//return "Too many failed login attempts";
		}
	}
}
