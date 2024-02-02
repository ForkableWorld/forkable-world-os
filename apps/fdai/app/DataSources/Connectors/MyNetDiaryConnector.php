<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\PasswordConnector;
use App\Products\AmazonHelper;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureDiastolicBottomNumberCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureSystolicTopNumberCommonVariable;
use DateTime;
use Exception;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\Exception\BadResponseException;
/** MyNetDiary test credentials.
 */
class MyNetDiaryConnector extends PasswordConnector {
    protected const DEVELOPER_CONSOLE = null;
    
    
    
    
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#4cd964';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Foods';
	public const DISPLAY_NAME = 'MyNetDiary';
	protected const ENABLED = 1;
    protected const GET_IT_URL = 'http://www.amazon.com/gp/product/B00BFEVFP4/ref=as_li_qf_sp_asin_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00BFEVFP4&linkCode=as2';
	public const ID = 12;
	public const IMAGE = 'https://i.imgur.com/yqm06Zg.png';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'MyNetDiary is an online and mobile food diary with calorie counter and online community. MyNetDiary provides instant and easy food entry, searching while you type. Enter foods 2-3 times faster than with any other food diary.';
	public const NAME = 'mynetdiary';
	protected const PREMIUM = false;
	protected const SHORT_DESCRIPTION = 'Tracks diet and exercise.';
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
    public static $BASE_API_URL = 'https://www.mynetdiary.com';
    private static $URL_LOGIN = 'https://www.mynetdiary.com/logon.do';
    private $endpoints;
	/**
	 * @throws Exception
	 */
    public function importData(): void {
	    $this->login();
        $fromTime = $this->getFromTime();
        $this->setEndPoints();
        $parseAll = $fromTime == 0;           // If $fromTime==0 parse history
        $period = ($fromTime == 0) ? 365 : 7; // If $fromTime==0 request annual report, else request weekly report
        $foodDiaryPeriod = ($fromTime == 0) ? 'period1y' : 'period7d';
        $timeDiffSeconds = time() - $fromTime;
        $timeDiffDays = ceil(($timeDiffSeconds / (60 * 60 * 24)) - 0.5);    // -0.5 to allow two syncs per day
        $url = $this->endpoints['foodDiary'];
        $formData = [
            'period'     => $foodDiaryPeriod,
            'periodFake' => 'period7d',
            'details'    => 'allFoods',
            'nutrients'  => 'trackedNutrients'
        ];
        $this->logDebug("MyNetDiary: Request to: $url");
	    $response = $this->post($url, $formData, ['Accept-Encoding' => 'gzip,deflate,sdch']);
        $measurementSets = $this->parseFoodDiary($response->getOriginalContent(), $fromTime, $parseAll);
        if(!empty($measurementSets)){
            $this->saveMeasurementSets($measurementSets);
        }
        /*
        **	Get diabetes and exercises measurements
        */
        $formData = [
            'numOfDays'       => $period,
            'checkedTrackers' => '0',
            'checkedLabels'   => '',
            'allTrackers'     => '',
            'allLabels'       => '',
            'event'           => 'period',
            'intervalNum'     => 0,
            'showEntryNotes'  => false
        ];
        $url = $this->endpoints['diabetesDiary2'];
        $this->logDebug("MyNetDiary: Request to: $url");
        try {
	        $response = $this->post($url, $formData, ['Accept-Encoding' => 'gzip,deflate,sdch']);
            $measurementSets = $this->parseDiabetesExerciseDiary($response->getOriginalContent(), $parseAll,
	            $timeDiffDays);
            if(!empty($measurementSets)){
                $this->saveMeasurementSets($measurementSets);
            }
        } catch (BadResponseException $e) {
            $this->logError('MyNetDiary: Bad response: '.$e->getMessage(), ['exception' => $e]);
        }
    }
    //$parseAll: true to fetch all the records if this is the history run, false otherwise
    //$days: number of days since the last update
	/**
	 * @param EntityBodyInterface $page
	 * @param $fromTime
	 * @param bool $parseAll
	 * @return MeasurementSet[]
	 * @throws Exception
	 */
    private function parseFoodDiary(EntityBodyInterface $page, $fromTime, bool $parseAll): array{
        $measurementsSets = [];
        $ingredients = [];
        $matches = [];
        $i = 1;
	    libxml_use_internal_errors(true);
	    $pqdoc = phpQuery::newDocument($page);
        $ingredient_cells = pq('#divReportColHeaders td:gt(0)', $pqdoc);
        foreach($ingredient_cells as $ingred_cell){
            if($i == 2 || (strpos(pq($ingred_cell)->text(), 'Time') !== false)){
                $i++;
                continue;
            } //ignore food score and time cells
            $html = pq($ingred_cell)->html();
            preg_match("#([\w\s\.-]+)\s*(?:<br/?>([\w%]+))?#", $html, $matches);
            $name = pq('span', $ingred_cell)->html();
            if(!$name){
                continue;
            }
            if(strpos($name, ',') !== false){
                [$name, $unit] = preg_split('/,\s?/u', $name);
                $name = trim($name);
                $unit = trim($unit);
            }else{
                $unit = 'count';
            }
            if($name == 'Calories'){
                $unit = 'kcal';
            }
            if($unit == '%'){
                $unit = '%RDA';
            }
            if(in_array($name, [
                'Calcium',
                'Potassium',
                'Zinc',
                'Water'
            ])){
                $name = "$name ($unit)";
            }
            $index = $i++;
            $ingredients[$index] = [
                'name' => $name,
                'unit' => $unit
            ];
        }
        //Find Days
        preg_match_all('/(<tr class="day">[\w\W]+?)(?=<tr class="day">|<\/tbody>)/', $page, $matches);
        $days = $matches[1];
        foreach($days as $day){
            $mealsRows = [];        //meals details of this day
            preg_match("/<span class='dailyDateNoLink'[^>]*>([^<]+)/", $day, $matches);
            $date = $matches[1];
            $dateParts = explode(',', $date);
            $now = new DateTime();
            $datetime = new DateTime($dateParts[1]);
            if($datetime > $now){
                $datetime->modify('-1 year');
            }
            $date = $datetime->format('Y-m-d');
            // if this is not a history run and the date of this day is less than the date of the last update,
            // skip this day
            if(!$parseAll && (strtotime($date) <= $fromTime)){
                continue;
            }
            //Find Meals
            preg_match('/(<tr>\s*<td class="meal"[^>]*>Breakfast[\w\W]*?)(?=<tr>\s*<td class="meal"[^>]*>Lunch|$)/', $day, $matches);
            if(isset($matches[1])){
                $mealsRows['Breakfast'] = [
                    'rows' => $this->cleanHtml($matches[1]),
                    'hour' => '8am'
                ];
            }
            preg_match('/(<tr>\s*<td class="meal"[^>]*>Lunch[\w\W]*?)(?=<tr>\s*<td class="meal"[^>]*>Dinner|$)/', $day, $matches);
            if(isset($matches[1])){
                $mealsRows['Lunch'] = [
                    'rows' => $this->cleanHtml($matches[1]),
                    'hour' => '12pm'
                ];
            }
            preg_match('/(<tr>\s*<td class="meal"[^>]*>Dinner[\w\W]*?)(?=<tr>\s*<td class="meal"[^>]*>Snacks|$)/', $day, $matches);
            if(isset($matches[1])){
                $mealsRows['Dinner'] = [
                    'rows' => $this->cleanHtml($matches[1]),
                    'hour' => '6pm'
                ];
            }
            preg_match('/(<tr>\s*<td class="meal"[^>]*>Snacks[\w\W]*)/', $day, $matches);
            if(isset($matches[1])){
                $mealsRows['Snacks'] = [
                    'rows' => $this->cleanHtml($matches[1]),
                    'hour' => '3pm'
                ];
            }
            foreach($mealsRows as $mealdetails){
                foreach($ingredients as $index => $ingredetails){
                    $meal_timestamp = strtotime($date.' '.$mealdetails['hour']);
                    preg_match_all('#<td>([\w\W]*?)</td>#', $mealdetails['rows'], $matches);
                    if(isset($matches[1][$index])){
                        $meal_value = (int)$matches[1][$index];
                        if($meal_value == 0){
                            continue;
                        }
                        $measurement = new QMMeasurement($meal_timestamp, $meal_value);
                        $measurementsSets[] = new MeasurementSet($ingredetails['name'], [$measurement], $ingredetails['unit'], 'Nutrients', $this->displayName, 'MEAN');
                    }
                }
                //Find Foods
                preg_match_all('#<tr>\s*<td>([^><]+)</td>\s*<td>[^><]*</td>\s*<td>(\d+)\w*</td>#', $mealdetails['rows'], $matches);
                for($i = 1, $iMax = count($matches[0]); $i < $iMax; $i++){
                    $food = $matches[1][$i];
                    $amount = (int)$matches[2][$i];
                    if($food && $amount){
                        $measurement = new QMMeasurement(strtotime($date . ' ' .$mealdetails['hour']), $amount);
                        $measurementsSets[] = new MeasurementSet($food, [$measurement], 'g', 'Foods', $this->displayName, 'MEAN');
                    }
                }
            }
        }
        return $measurementsSets;
    }
    //$parseAll: true to fetch all the records if this is the history run, false otherwise
    //$days: the days since the last update
    /**
     * @param EntityBodyInterface $page
     * @param bool $parseAll
     * @param int $days
     * @return MeasurementSet[]
     */
    private function parseDiabetesExerciseDiary(EntityBodyInterface $page, bool $parseAll, int $days = 1): array{
        $matches = [];
        $measurementsSets = [];
	    libxml_use_internal_errors(true);
	    $doc = phpQuery::newDocument($page);
        $rows = pq('tr:gt(0)', $doc);
        $i = 1;
        foreach($rows as $row){
            if(!$parseAll && $i++ > $days){
                break;
            }
            $cells = pq('td', $row);
            $date = '';
            foreach($cells as $cell){
                if(strtotime(pq($cell)->text())){
                    $date = pq($cell)->text();
                }
                $trackers = pq('div', $cell);
                foreach($trackers as $tracker){
                    if(strpos(pq($tracker)->text(), 'kcal') !== false){  //This is exercise entry
                        preg_match('#(\d+:\d+(?:PM|AM))?\s*<strong>([\w\W]+)</strong>:\s*([\d\.]+)?([\w]+) (\d+)kcal#', pq($tracker)->html(), $matches);
                        if(!isset($matches[0])){
                            //if no matches found, skip
                            continue;
                        }
                        $time = $matches[1] ?? '';
                        $name = trim($matches[2]);
                        // if interval undefined and unit defined, then interval equals one
                        $interval = isset($matches[3]) ? (int)$matches[3] : 1;
                        $matches[4] = trim($matches[4]);
                        $unit = $matches[4] == 'hour' ? 'h' : $matches[4];
                        $cals = (int)$matches[5];
                        if($cals == 0 || empty($name)){
                            continue;
                        }
                        $timestamp = strtotime("$date $time");
                        $measurement = new QMMeasurement($timestamp, $interval);
                        $measurementsSets[] = new MeasurementSet($name, [$measurement], $unit, 'Physical Activity', $this->displayName, 'MEAN');
                        $measurement = new QMMeasurement($timestamp, $cals);
                        $measurementsSets[] = new MeasurementSet('Calories Burned', [$measurement], 'kcal', 'Physical Activity', $this->displayName, 'MEAN');
                    }else{
                        //This is diabetes entry
                        preg_match('#(\d+:\d+(?:PM|AM))?\s*<strong>([\w\W]+)</strong>:\s*([\d\./]+)([\w/%]+)?#', pq($tracker)->html(), $matches);
                        if(!isset($matches[0])){
                            //if no matches found, skip
                            continue;
                        }
                        $time = $matches[1];
                        $name = trim($matches[2]);
                        // if unit undefined, set unit to 'count'
                        $unit = isset($matches[4]) ? ($matches[4] === 'lbs' ? 'lb' : $matches[4]) : 'count';
                        $timestamp = strtotime("$date $time");
                        if(strpos($name, 'pressure') !== false){
                            // This is a blood pressure tracker
                            $arr = explode('/', $matches[3]);
                            $measurement = new QMMeasurement($timestamp, (int)$arr[0]);
                            $measurementsSets[] = new MeasurementSet(BloodPressureSystolicTopNumberCommonVariable::NAME,
                                                                     [$measurement], $unit, 'Vital Signs', $this->displayName, 'MEAN');
                            $measurement = new QMMeasurement($timestamp, (int)$arr[1]);
                            $measurementsSets[] = new MeasurementSet(BloodPressureDiastolicBottomNumberCommonVariable::NAME,
                                                                     [$measurement], $unit, 'Vital Signs', $this->displayName, 'MEAN');
                        }else{
                            $val = (int)$matches[3];
                            if($val == 0){
                                continue;
                            }
                            $measurement = new QMMeasurement($timestamp, $val);
                            $measurementsSets[] = new MeasurementSet($name, [$measurement], $unit,
                                                                     'Vital Signs', $this->displayName, 'MEAN');
                        }
                    }
                }
            }
        }
        return $measurementsSets;
    }
    /**
     * @param string $html
     * @return string
     */
    private function cleanHtml(string $html): string{
        return preg_replace([
            "#\w+='[\w\W]+?'#",
            '#\w+="\w+"#',
            '#&nbsp;#',
            '#\s*(?=>)#',
            '#<b>#',
            '#</b>#',
            '#<img\s*/>#'
                            ], '', $html);
    }
    public function setEndPoints(): void{
        $this->endpoints = [
            'foodDiary'      => 'https://www.mynetdiary.com/reportRefresh.do',
            'exerciseDiary'  => 'https://www.mynetdiary.com/reportRefresh.do',
            'diabetesDiary'  => 'https://www.mynetdiary.com/dailyTracking.do?date=',
            'diabetesDiary2' => 'http://www.mynetdiary.com/loadDayParts.do'
        ];
    }
    public function makeSerializable(){
        parent::makeSerializable();
        $this->endpoints = null;
    }
	/**
	 * @throws ConnectorConnectionException
	 */
	protected function login(): void{
		// Post login parameters
		$response = $this->post(self::$URL_LOGIN, [
			'logonName' => $this->getConnectorUserName(),
			'password' => $this->getConnectorPassword(),
			'cmdOK' => 'Secure Sign In'
		], ['allow_redirects' => false]);
		// Check if we can redirect to daily.do, if not the username or password was incorrect
		if(strpos($response->headers->all(), 'daily.do') !== false){
			// Set the cookies we got to the root, as if we went to mynetdiary.com/ before anything else
			$cookies = $this->getCookies();
			foreach($cookies as $cookie){
				$cookie->setPath('/');
			}
			return;
		}
		$this->handleFailedLogin();
	}
}
