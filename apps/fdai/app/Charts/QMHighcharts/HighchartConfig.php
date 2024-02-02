<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Cards\QMCard;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\Options\BaseCredits;
use App\Charts\QMHighcharts\Options\BaseFillColor;
use App\Charts\QMHighcharts\Options\BaseLinearGradient;
use App\Charts\QMHighcharts\Options\BaseLoading;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use App\CodeGenerators\JsonToPhp\PhpGenerator;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Http\Controllers\ChartsController;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
use App\Storage\S3\S3Public;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use Illuminate\Support\Str;
use Illuminate\View\View;
class HighchartConfig {
	const THEME_WHITE = 'white';
	const THEME_TRANSPARENT = 'transparent';
	const THEME_DARK = 'dark';
	public const COLORS_FOR_WHITE_BACKGROUND = [
		QMColor::HEX_BLACK,
		QMColor::HEX_GOOGLE_BLUE,
		QMColor::HEX_GOOGLE_RED,
		QMColor::HEX_GOOGLE_GREEN,
		QMColor::HEX_GOOGLE_YELLOW,
		QMColor::HEX_GOOGLE_PLUS_RED,
		QMColor::HEX_PURPLE,
		//QMColor::HEX_ARMY_GREEN,
		//QMColor::HEX_CYAN,
	];
	public const COLORS_FOR_DARK_BACKGROUND = [
		QMColor::HEX_WHITE,
		QMColor::HEX_GOOGLE_RED,
		QMColor::HEX_GOOGLE_BLUE,
		QMColor::HEX_GOOGLE_GREEN,
		QMColor::HEX_GOOGLE_YELLOW,
		QMColor::HEX_GOOGLE_PLUS_RED,
		QMColor::HEX_PURPLE,
		//QMColor::HEX_ARMY_GREEN,
		//QMColor::HEX_CYAN,
	];
	public const COLORS_FOR_DARK_BACKGROUND_EXCLUDING_WHITE = [
		QMColor::HEX_GOOGLE_RED,
		QMColor::HEX_GOOGLE_BLUE,
		QMColor::HEX_GOOGLE_GREEN,
		QMColor::HEX_GOOGLE_YELLOW,
		QMColor::HEX_GOOGLE_PLUS_RED,
		QMColor::HEX_PURPLE,
		//QMColor::HEX_ARMY_GREEN,
		//QMColor::HEX_CYAN,
	];
	const TYPE_HIGHCHART = 'HIGHCHART';
	const TYPE_HIGHSTOCK = 'HIGHSTOCK';
	const TYPE_HIGHMAPS = 'HIGHMAPS';
	const ENGINE_JQUERY = 10;
	const ENGINE_MOOTOOLS = 11;
	const ENGINE_PROTOTYPE = 12;
public const DEFAULT_CHART_HEIGHT = 400;
	/**
	 * @var Chart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var Title
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var Subtitle
	 * @link https://api.highcharts.com/highcharts/subtitle
	 */
	public $subtitle;
	/**
	 * @var XAxisTimeLine
	 * @link https://api.highcharts.com/highcharts/xAxis
	 */
	public $xAxis;
	/**
	 * @var YAxis
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var Legend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var Series[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series = [];
	/**
	 * @var Tooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	public $colors = self::COLORS_FOR_WHITE_BACKGROUND;
	/**
	 * @var BaseCredits
	 */
	public $credits;
	public $lang = ['loading' => ''];
	public $loading;
	/**
	 * @var PlotOptions
	 */
	public $plotOptions;
	public $useHighStocks = false;
	/**
	 * @var Exporting
	 */
	public $exporting;
	public $id;
	public $themeName = self::THEME_WHITE;
	/**
	 * @var int
	 */
	public $divHeight;
	protected $qmChart;
	/**
	 * The chart type.
	 * Either self::HIGHCHART or self::HIGHSTOCK
	 * @var int
	 */
	protected $_chartType = self::TYPE_HIGHCHART;
	/**
	 * The javascript library to use.
	 * One of ENGINE_JQUERY, ENGINE_MOOTOOLS or ENGINE_PROTOTYPE
	 * @var int
	 */
	protected $_jsEngine;
	/**
	 * Array with keys from extra scripts to be included
	 * @var array
	 */
	protected $_extraScripts = [];
	/**
	 * Any configurations to use instead of the default ones
	 * @var array An array with same structure as the config.php file
	 */
	protected $_confs = [];
	/**
	 * Any theme to use instead of the default one
	 * Basically, copy everything below the `import Highcharts from '../parts/Globals.js`';` statement
	 * from example themes like the one at https://www.highcharts.com/demo/line-basic/dark-unica
	 * @var string
	 */
	protected $arguments = [];
	protected $cardBackgroundColor = QMColor::HEX_BLUE;
	protected $cardBody;
	protected $css = "";
	protected $fontAwesome;
	protected $footer;
	protected $minMaxBuffer;
	protected $queryParams = [];
	protected $rawData;
		protected $url; // Any larger gets cut off on mobile
	protected $whereClauseStrings;
	public string $type;
	/**
	 * HighchartConfig constructor.
	 * @param QMChart|null $QMChart
	 */
	public function __construct(QMChart $QMChart = null){
		if(!$this->qmChart){
			$this->qmChart = $QMChart;
		}
		$this->chart = new Chart();
		$this->title = new Title();
		$this->subtitle = new Subtitle();
		$this->yAxis = new YAxis();
		$this->legend = new Legend();
		$this->setSeries([]);
		$this->exporting = new Exporting();
		$this->credits = new Credits();
		$this->loading = new BaseLoading();
		if($QMChart){
			if(method_exists($QMChart, 'getUnitName')){
				$this->setUnit($QMChart->getUnitName());
			}
			$this->setTitle($QMChart->getTitleAttribute());
			$this->setSubtitle($QMChart->getSubtitleAttribute());
		}

		if(!isset($this->type)){
			$class = static::class;
			if($QMChart){$class = get_class($QMChart);}
			$class = QMStr::toShortClassName($class);
			$type = str_replace("QM", "", $class);
			$type = str_replace("Highstock",  " Chart", $type);
			$type = str_replace("Highchart",  " Chart", $type);
			$type = str_replace("Config",  "", $type);
			$type = QMStr::classToTitle($type);
			$this->type = $type;
		}
		if($QMChart && $this->type === "Chart"){le("stupid chart type");}
	}
	public function setUnit(string $unitName){
		$this->getTooltip()->setValueSuffix($unitName);
	}
	/**
	 * @return Tooltip
	 */
	public function getTooltip(): Tooltip{
		if(!$this->tooltip){
			$this->tooltip = new Tooltip();
		}
		return $this->tooltip;
	}
	public function setTitle(string $title): self{
		$this->getHCTitle()->setText($title);
		if($title){
			$this->setId(QMStr::slugify($title));
		}
		return $this;
	}
	public function getHCTitle(): Title{
		$this->title = Title::instantiateIfNecessary($this->title);
		return $this->title;
	}
	public static function uploadThemes(){
		S3Public::uploadFile('js/highcharts-themes/highcharts-themes.js',
		                     'public/js/highcharts-themes/highcharts-themes.js', true);
	}
	/**
	 * @param string $table
	 * @param string $field
	 * @param array $params
	 * @param string $color
	 * @param string|null $title
	 * @param string|null $subTitle
	 * @return string
	 */
	public static function getHtmlForField(string $table, string $field, array $params = [], string $color = 'blue',
	                                       string $title = null, string $subTitle = null): string{
		$chart = new AggregatedColumnHighstockChart($table, $field, $params, $title, $subTitle);
		$chart->setBackgroundColor($color);
		$html = $chart->inlineNoHeading();
		return $html;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static|null|bool
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if($arrayOrObject instanceof static){
			if(!$arrayOrObject->chart instanceof Chart){
				$arrayOrObject->chart = new Chart($arrayOrObject->chart);
			}
			return $arrayOrObject;
		}
		$model = new static();
		foreach($arrayOrObject as $key => $value){
			if($value === []){
				continue;
			} // Empty arrays break charts
			if($value !== null){
				if($key === "chart" && !$value instanceof Chart){
					$model->$key = new Chart($value);
				} else{
					$model->$key = $value;
				}
			}
		}
		return $model;
	}
	public static function generateExamples(){
		$files = FileFinder::listFiles('vendor/ghunti/highcharts-php/demos', true);
		foreach($files as $sourcePath){
			if(!str_contains($sourcePath, '.php')){
				continue;
			}
			$relativeSourcePath = FileHelper::getRelativePath($sourcePath);
			require $sourcePath;
			$folders = QMStr::between($sourcePath, 'demos', '.');
			$folders = explode('/', $folders);
			foreach($folders as $key => $value){
				if(empty($value)){
					unset($folders[$key]);
				} else{
					$folders[$key] = QMStr::toClassName($value);
				}
			}
			$examplesNS = 'App\Charts\QMHighcharts\\'.implode('\\', $folders);
			$filename = FileHelper::getFileNameWithoutExtension($relativeSourcePath, '/');
			$parentClass = QMStr::toClassName($filename);
			/** @noinspection PhpUnhandledExceptionInspection */
			$exampleContents = FileHelper::getContents($sourcePath);
			/** @noinspection HtmlRequiredLangAttribute */
			$html = "<html>".QMStr::after("<html>", $exampleContents);
			$html = str_replace('$chart', '$this', $html);
			$contents = QMStr::between($exampleContents, "<?php", "?>");
			$contents = str_replace("Yaxis = new HighchartOption", "Yaxis = new BaseYAxis", $contents);
			$contents = str_replace("\n", "\n\t\t", $contents);
			$constructor = QMStr::after("chart();", $contents);
			if(empty($constructor)){
				$constructor = QMStr::after("chart(Highchart::HIGHSTOCK);", $contents);
			}
			$constructor = str_replace('$chart', '$this', $constructor);
			$json2PHP = new PhpGenerator(true, true, $examplesNS, true);
			$json2PHP->setBaseClass(HighchartConfig::class);
			$json2PHP->setPropertyBaseClass(HighchartOption::class);
			$json2PHP->setConstructor($constructor);
			$json2PHP->setExtraFunctions("
    public function demo(): string {
        /** @noinspection PhpIncludeInspection */
        require base_path('$relativeSourcePath');
    }
            ");
			$json2PHP->setSubPropertyNamespace('App\Charts\QMHighcharts\Options');
			if(strpos($exampleContents, "new HighchartOption") !== false){
				$json2PHP->addUsedClass(HighchartOption::class);
			}
			if(strpos($exampleContents, "new HighchartJsExpr") !== false){
				$json2PHP->addUsedClass(HighchartJsExpr::class);
			}
		}
	}
	/**
	 * @param array $array
	 * @return static
	 */
	public static function __set_state(array $array): self{
		$object = new static;
		foreach($array as $key => $value){
			$object->{$key} = $value;
		}
		return $object;
	}
	/**
	 * @param string $name transparent, dark
	 * @return string
	 */
	public static function getThemeContents(string $name): string{
		try {
			$str = FileHelper::getContents("public/js/highcharts-themes/$name-highstock-theme.js");
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return "
            <script type=\"text/javascript\">
                Highcharts.createElement('link', {
                    href: 'https://fonts.googleapis.com/css?family=Unica+One',
                    rel: 'stylesheet',
                    type: 'text/css'
                }, null, document.getElementsByTagName('head')[0]);
                $str
                // Apply the theme
                Highcharts.setOptions(Highcharts.theme);
            </script>
        ";
	}
	public static function getUrlFolder(): string{
		return "charts";
	}
	/**
	 * @return YAxis
	 */
	public function getYAxis(): YAxis{
		if(!$this->yAxis instanceof YAxis){
			$this->yAxis = new YAxis();
		}
		return $this->yAxis;
	}
	public function setWhiteBackgroundTheme(): void{
		$this->setThemeName(self::THEME_WHITE);
		$this->setColorPalette(self::COLORS_FOR_WHITE_BACKGROUND);
	}
	/**
	 * @param array $palette
	 */
	public function setColorPalette(array $palette): void{
		$this->setColors($palette);
	}
	/**
	 * @param array $colors
	 */
	public function setColors(array $colors): void{
		if(count($colors) < 2){
			le("colors ", $colors);
		}
		$this->colors = $colors;
	}
	/**
	 * @param string $css
	 */
	public function setCss(string $css): void{
		$this->css = $css;
	}
	/**
	 * @param string $titleText
	 */
	public function setXAxisTitleText(string $titleText): void{
		$this->getXAxis()->title->text = $titleText;
	}
	/**
	 * @return XAxisTimeLine
	 */
	public function getXAxis(): BaseXAxis{
		if(!$this->xAxis){
			$this->xAxis = new BaseXAxis();
		}
		return $this->xAxis;
	}
	/**
	 * @param array $categories
	 */
	public function setXAxisCategories(array $categories): void{
		$this->getXAxis()->setCategories($categories);
	}
	/**
	 * @param string $seriesName
	 * @param array $seriesData
	 * @param float|null $min
	 * @param float|null $max
	 * @deprecated Use add series
	 */
	public function addSeriesArray(string $seriesName, array $seriesData, float $min = null, float $max = null): void{
		if($min || $max){
			$this->setYAxisMinMax($min, $max);
		}
		$series = new Series();
		$series->name = $seriesName;
		$series->data = $seriesData;
		$this->addSeriesAndYAxis($series);
	}
	/**
	 * @param float $min
	 * @param float $max
	 * @param float $buffer
	 */
	public function setYAxisMinMax(float $min, float $max, float $buffer = 0.01): void{
		$bufferedMin = $min - $buffer * ($max - $min);
		if(!isset($this->yAxis->min) || $this->yAxis->min > $bufferedMin){
			$this->yAxis->min = $bufferedMin;
		}
		$bufferedMax = $max + $buffer * ($max - $min);
		if(!isset($this->yAxis->max) || $this->yAxis->max < $bufferedMax){
			$this->yAxis->max = $bufferedMax;
		}
	}
	/**
	 * @param BaseSeries $series
	 */
	public function addSeriesAndYAxis(BaseSeries $series): void{
		$this->addSeriesAndSetColor($series);
		if(method_exists($series, 'getYAxis')){
			$yAxisObj = $series->getYAxis();
			if($yAxisObj){
				$this->addYAxis($yAxisObj, $series->yAxis);
			}
		}
	}
	/**
	 * @param BaseSeries $series
	 */
	public function addSeriesAndSetColor(BaseSeries $series): void{
		$index = count($this->series);
		$color = $this->colors[$index] ?? QMColor::HEX_BLACK;
		$series->setColor($color);
		$this->addSeries($series);
	}
	/**
	 * @param BaseSeries $series
	 */
	public function addSeries(BaseSeries $series): void{
		$this->series[] = $series;
	}
	/**
	 * @param HighstockYAxis $yAxis
	 * @param int $index
	 * @return $this
	 */
	public function addYAxis(HighstockYAxis $yAxis, int $index): HighchartConfig{
		if(!is_array($this->yAxis)){
			$this->yAxis = [];
		}
		if($this->yAxis){
			$yAxis->opposite = true;
		}
		$this->yAxis[$index] = $yAxis;
		ksort($this->yAxis);
		$this->validate();
		return $this;
	}
	public function validate(): void{
		if(isset($this->loading->style)){
			le('isset($this->loading->style)');
		}
		//self::assertStyleIsNotArray($this);
		if(!$this->chart instanceof Chart){
			le('!$this->chart instanceof Chart');
		}
		if(is_array($this->chart)){
			le('is_array($this->chart');
		}
		if(!is_object($this->exporting)){
			le("Exporting is ".\App\Logging\QMLog::print_r($this->exporting, true));
		}
		$axes = $this->yAxis ?? null;
		$series = $this->series;
		if($series && is_array($series)){
			foreach($series as $s){
				//if(!$s->marker){le('!$s->marker');}
				if(isset($this->tooltip) && isset($s->tooltip)){
					le("Chart should not have tooltip if series has tooltip");
				}
				$index = $s->yAxis ?? null;
				if($index && count($series) > 1){
					if(!is_int($index)){
						le("series->yAxis should be an index");
					}
					if(!is_array($axes)){
						le("yAxis should be an array");
					}
					if(!isset($axes[$index])){
						le("Missing yAxis $index");
					}
				}
			}
		}
		if(!$this->chart->renderTo){
			le("chart is ".\App\Logging\QMLog::print_r($this->chart, true));
		}
	}
	/**
	 * @param string $title
	 * @param string $subtitle
	 * @return HighchartConfig|\stdClass
	 */
	public function getExportableConfig(string $title, string $subtitle): \stdClass{
		$this->validate();
		$config = clone $this;
		$config->setTitle($title);
		$config->subtitle->text = $subtitle;
		$config->chart->plotBackgroundImage = false; // SVG was really big and doesn't even work
		$config->setUseHighStocks(false);
		if($config->series){
			foreach($config->series as $series){
				unset($series->type); // type required for highstock but breaks exports
			}
		}
		$config->loading = null;  // Don't use unset or it breaks tests
		$config->getPlotOptions();// plotOptions can't be set on highstock but is required for exports
		$this->validate();
		$encoded = json_encode($config);
		if(!$encoded){
			le("Could not json_encode ".__CLASS__, $config);
		}
		return json_decode($encoded);
	}
	/**
	 * @param bool $value
	 */
	public function setUseHighStocks(bool $value = true): void{
		if($value){
			$this->unsetPlotOptions();
		}
		$this->useHighStocks = $value;
	}
	public function unsetPlotOptions(): void{
		unset($this->plotOptions); // It seems like we can't have plotOptions field with highstock!
	}
	public function getPlotOptions(): PlotOptions{
		if(!isset($this->plotOptions)){
			return $this->plotOptions = new PlotOptions();
		}
		return $this->plotOptions = PlotOptions::instantiateIfNecessary($this->plotOptions);
	}
	/**
	 * @param PlotOptions|CandlestickPlotOptions $plotOptions
	 */
	public function setPlotOptions($plotOptions): void{
		$this->plotOptions = $plotOptions;
	}
	public function getConstructor(): string{
		return "chart";
	}
	/**
	 * Prints javascript script tags for all scripts that need to be included on page
	 * @param boolean $return if true it returns the scripts rather then echoing them
	 * @return string
	 */
	public function printScripts(bool $return = false): ?string{
		$scripts = '';
		foreach($this->getScripts() as $script){
			$scripts .= '<script type="text/javascript" src="'.$script.'"></script>';
		}
		if($return){
			return $scripts;
		} else{
			echo $scripts;
		}
		return null;
	}
	/**
	 * Finds the javascript files that need to be included on the page, based
	 * on the chart type and js engine.
	 * Uses the conf.php file to build the files path
	 * @return array The javascript files path
	 */
	public function getScripts(): array{
		$scripts = [];
		switch($this->_jsEngine) {
			case self::ENGINE_JQUERY:
				$scripts[] = $this->_confs['jQuery']['path'].$this->_confs['jQuery']['name'];
				break;
			case self::ENGINE_MOOTOOLS:
				$scripts[] = $this->_confs['mootools']['path'].$this->_confs['mootools']['name'];
				if($this->_chartType === self::TYPE_HIGHCHART){
					$scripts[] = $this->_confs['highchartsMootoolsAdapter']['path'].
					             $this->_confs['highchartsMootoolsAdapter']['name'];
				} else{
					$scripts[] = $this->_confs['highstockMootoolsAdapter']['path'].
					             $this->_confs['highstockMootoolsAdapter']['name'];
				}
				break;
			case self::ENGINE_PROTOTYPE:
				$scripts[] = $this->_confs['prototype']['path'].$this->_confs['prototype']['name'];
				if($this->_chartType === self::TYPE_HIGHCHART){
					$scripts[] = $this->_confs['highchartsPrototypeAdapter']['path'].
					             $this->_confs['highchartsPrototypeAdapter']['name'];
				} else{
					$scripts[] = $this->_confs['highstockPrototypeAdapter']['path'].
					             $this->_confs['highstockPrototypeAdapter']['name'];
				}
				break;
		}
		switch($this->_chartType) {
			case self::TYPE_HIGHCHART:
				$scripts[] = $this->_confs['highcharts']['path'].$this->_confs['highcharts']['name'];
				break;
			case self::TYPE_HIGHSTOCK:
				$scripts[] = $this->_confs['highstock']['path'].$this->_confs['highstock']['name'];
				break;
			case self::TYPE_HIGHMAPS:
				$scripts[] = $this->_confs['highmaps']['path'].$this->_confs['highmaps']['name'];
				break;
		}
		//Include scripts with keys given to be included via includeExtraScripts
		if(!empty($this->_extraScripts)){
			foreach($this->_extraScripts as $key){
				$scripts[] = $this->_confs['extra'][$key]['path'].$this->_confs['extra'][$key]['name'];
			}
		}
		return $scripts;
	}
	/**
	 * Manually adds an extra script to the extras
	 * @param string $key key for the script in extra array
	 * @param string $filepath path for the script file
	 * @param string $filename filename for the script
	 */
	public function addExtraScript(string $key, string $filepath, string $filename){
		$this->_confs['extra'][$key] = [
			'name' => $filename,
			'path' => $filepath,
		];
	}
	/**
	 * Signals which extra scripts are to be included given its keys
	 * @param array $keys extra scripts keys to be included
	 */
	public function includeExtraScripts(array $keys = []){
		$this->_extraScripts = empty($keys) ? array_keys($this->_confs['extra']) : $keys;
	}
	/**
	 * @param string $title
	 * @return $this
	 */
	public function setYAxisTitle(string $title): HighchartConfig{
		if(is_array($this->yAxis)){
			le("yAxis is array");
		}
		$this->yAxis->title->text = $title;
		return $this;
	}
	/**
	 * @param string $id
	 * @return $this
	 */
	public function setRenderTo(string $id): HighchartConfig{
		if(is_array($this->chart)){
			le('is_array($this->chart)');
		}
		$this->chart->renderTo = $id."-chart-container";
		return $this;
	}
	/**
	 * @param bool $value
	 * @return static
	 */
	public function setXAxisOrdinal(bool $value): HighchartConfig{
		$this->getXAxis()->ordinal = $value;
		return $this;
	}
	public function getUrl(array $params = []): string{
		$params = array_merge($params, $this->arguments);
		$params['class'] = static::class;
		return UrlHelper::addParams(QMRequest::origin().'/'.ChartsController::CHARTS_PATH, $params);
	}
	/**
	 * @param string $url
	 * @return static
	 */
	public function setUrl(string $url): HighchartConfig{
		$this->url = $url;
		return $this;
	}
	public function getCardHtml(): string{
		$this->setTransparentTheme();
		$chartHtml = $this->inlineNoHeading();
		$gradient = $this->getCardBackgroundGradientColorCss();
		$body = ($this->url) ? $this->getCardBody() : '';
		return "
            <div class=\"card card-chart\">
                <div class=\"card-header card-header-success\" style=\"$gradient\">
                <div class=\"ct-chart\">
                    $chartHtml
                </div>
            </div>
                <div class=\"card-body\">
                    $body
                </div>
                $this->footer
            </div>
        ";
	}
	public function setTransparentTheme(): self{
		$this->setThemeName(self::THEME_TRANSPARENT);
		$this->setBackgroundColor(self::THEME_TRANSPARENT);
		$textColor = $this->colors[0];
		$this->replaceSeriesColors($textColor);  // This is
		return $this;
	}
	/**
	 * @param string $color
	 * @return $this
	 */
	public function setBackgroundColor(string $color): HighchartConfig{
		if(QMColor::isWhite($color)){
			$this->setColorPalette(self::COLORS_FOR_WHITE_BACKGROUND);
		} else{
			$this->setColorPalette(self::COLORS_FOR_DARK_BACKGROUND);
		}
		$this->chart->backgroundColor = BaseHighstock::generateBackgroundGradientFill($color);
		return $this;
	}
	/**
	 * @param string $color
	 * @return BaseFillColor|string
	 */
	public static function generateBackgroundGradientFill(string $color){
		if($color === self::THEME_TRANSPARENT){
			return 'rgba(0,0,0,0)';
		}
		$arr = QMColor::toGradient($color);
		$obj = new BaseFillColor();
		$linearGradient = new BaseLinearGradient();
		$linearGradient->x1 = 0;
		$linearGradient->y1 = 0;
		$linearGradient->x2 = 1;
		$linearGradient->y2 = 1;
		$obj->linearGradient = $linearGradient;
		$obj->stops = [
			[
				0,
				$arr[0],
			],
			[
				1,
				$arr[1],
			],
		];
		return $obj;
	}
	/**
	 * @param $textColor
	 */
	public function replaceSeriesColors($textColor): void{
		//le("Text color should be set in theme!");
		//$this->setTitleColor($textColor);
		//$this->setSubtitleColor($textColor);
		foreach($this->getSeries() as $series){
			if(!$series instanceof BaseSeries){
				le('!$series instanceof BaseSeries');
			}
			$color = $this->colors[$series->yAxis] ?? $textColor;
			if(!in_array($series->getColor(), $this->colors)){
				$series->setColor($color);
			}
		}
		if(isset($this->yAxis)){
			$axes = $this->getYAxesArray();
			foreach($axes as $key => $axi){
				if(!$axi){
					continue;
				}
				$color = $this->colors[$key] ?? $textColor;
				$axi->setColor($color);
				if(!in_array($axi->getColor(), $this->colors)){
					$axi->setColor($color);
				}
			}
		}
	}
	/**
	 * @return Series[]
	 */
	public function getSeries(): array{
		foreach($this->series as $i => $one){
			$this->series[$i] = Series::instantiateIfNecessary($one);
		}
		return $this->series;
	}
	/**
	 * @param Series[] $series
	 */
	public function setSeries(array $series): void{
		foreach($series as $one){
			if(!$one instanceof BaseSeries){
				le('!$one instanceof BaseSeries');
			}
		}
		$this->series = $series;
	}
	/**
	 * @return YAxis[]
	 */
	public function getYAxesArray(): array{
		if(!isset($this->yAxis) || !$this->yAxis){
			return [];
		}
		if(is_array($this->yAxis) && isset($this->yAxis[0])){
			foreach($this->yAxis as $i => $axi){
				$this->yAxis[$i] = YAxis::instantiateIfNecessary($axi);
			}
			return $this->yAxis;
		}
		$this->yAxis = YAxis::instantiateIfNecessary($this->yAxis);
		return [$this->yAxis];
	}
	public function inlineNoHeading(): string{
		$renderToDiv = $this->renderToDiv();
		$script = $this->inlineScript();
		return $this->addStyleTag("
$renderToDiv
$script
");
	}
	protected function renderToDiv(): string{
		$renderTo = $this->renderToId();
		// Set height of container instead of chart or full-screen mode doesn't work
		$height = $this->getDivHeight();
		return "<div id=\"$renderTo\" style=\"height: ".$height."px;\"></div>";
	}
	/**
	 * @return string
	 */
	public function renderToId(): string{
		return $this->chart->renderTo;
	}
	/**
	 * @return int
	 */
	public function getDivHeight(): int{
		return $this->divHeight ?? self::DEFAULT_CHART_HEIGHT;
	}
	public function setDivHeight(int $height){
		$this->divHeight = $height;
	}
	protected function inlineScript(): string{
		$script = $this->scriptContents();
		return "
<script type=\"text/javascript\" defer>
    $script
</script>
";
	}
	public function scriptContents(): string{
		$this->prepareForRender();
		$rendered = $this->renderChart();
		$themeName = $this->getThemeName();
		$this->setHeight(null);  // We set height of div container instead of chart or full-screen mode doesn't work
		return "
$(function(){
    Highcharts.theme = Highcharts.themes.$themeName;
    Highcharts.setOptions(Highcharts.theme);
    window.chart = $rendered
});
        ";
	}
	protected function prepareForRender(): void{
		if($this->hasMultipleSeries()){
			$this->setLegendEnabled(true);
		}
		if($this->useHighStocks){
			$this->unsetPlotOptions();
		} else{
			$this->getPlotOptions();
		}
		$this->validate();
	}
	public function hasMultipleSeries(): bool{
		if(!is_array($this->series)){
			return false;
		}
		return count($this->series) > 1;
	}
	public function setLegendEnabled(bool $val): void{
		$this->legend->enabled = $val;
	}
	/**
	 * Render the chart and returns the javascript that must be printed to the page to create the chart
	 * @param string|null $varName The javascript chart variable name
	 * @param string|null $callback The function callback to pass to the Highcharts.Chart method
	 * @param boolean $withScriptTag It renders the javascript wrapped in html script tags
	 * @return string The javascript code
	 */
	public function renderChart(string $varName = null, string $callback = null, bool $withScriptTag = false): string{
		$result = '';
		if(!is_null($varName)){
			$result = "$varName = ";
		}
		$result .= 'new Highcharts.';
		if($this->useHighStocks){
			$result .= 'StockChart(';
		} elseif($this->_chartType === self::TYPE_HIGHMAPS){
			$result .= 'Map(';
		} else{
			$result .= 'Chart(';
		}
		$result .= $this->renderOptions();
		$result .= is_null($callback) ? '' : ", $callback";
		$result .= ');';
		if($withScriptTag){
			$result = '<script type="text/javascript">'.$result.'</script>';
		}
		$this->validateChartHtml($result);
		return $result;
	}
	/**
	 * Render the chart options and returns the javascript that
	 * represents them
	 * @return string The javascript code
	 */
	public function renderOptions(): string{
		$this->validate();
		$options = $this->getOptions();
		if(!isset($options['tooltip'])){
			unset($options['tooltip']);
		}
		$jsExpressions = [];
		//Replace any js expression with random strings so we can switch them back after json_encode the options
		$options = static::_replaceJsExpr($options, $jsExpressions);
		$json = QMStr::prettyJsonEncode($options); //Replace any js expression on the json_encoded string
		if(strpos($json, '"style": []')){
			le($json);
		}
		if(strpos($json, '"style": null')){
			le('"style": null');
		}
		foreach($jsExpressions as $key => $expr){
			$json = str_replace('"'.$key.'"', $expr, $json);
		}
		if(!str_contains($json, '"filename":')){
			le('"filename":');
		}
		if(str_contains($json, '"tooltip": null,')){
			le('"tooltip": null,');
		}
		return $json;
	}
	/**
	 * @return array
	 */
	public function getOptions(): array{
		if(!isset($this->chart->plotBackgroundImage)){
			$this->chart->plotBackgroundImage = false;
		}
		if($t = $this->title->text){
			$this->title->text = QMStr::titleCaseSlow($t);
		}
		if(!isset($this->exporting->filename)){
			$this->exporting->filename = $this->id;
		}
		$this->unsetEmptyStyles();
		$options = json_decode(json_encode($this), true);
		foreach($options as $key => $value){
			if($value === []){
				unset($options[$key]);
			}
		}
		if(empty($options["xAxis"]["title"]["style"])){
			unset($options["xAxis"]["title"]["style"]);
		}
		if(empty($options["yAxis"]["title"]["style"])){
			unset($options["yAxis"]["title"]["style"]);
		}
		if(empty($options["title"]["style"])){
			unset($options["title"]["style"]);
		}
		ksort($options);
		return $options;
	}
	public function unsetEmptyStyles(){
		if(empty($this->title->style)){
			unset($this->title->style);
		}
		if(empty($this->subtitle->style)){
			unset($this->subtitle->style);
		}
		if(empty($this->loading->style)){
			unset($this->loading->style);
		}
		$axes = $this->getYAxesArray();
		foreach($axes as $axe){
			if(empty($axe->title->style)){
				unset($axe->title->style);
			}
		}
		if(isset($this->xAxis)){
			$x = $this->xAxis;
			if(empty($x->title->style)){
				unset($x->title->style);
			}
		}
	}
	/**
	 * Replaces any HighchartJsExpr for an id, and save the js expression on the jsExpressions array
	 * Based on Zend_Json
	 * @param mixed $data The data to analyze
	 * @param array &$jsExpressions The array that will hold information about the replaced js expressions
	 * @return array|HighchartJsExpr|mixed|string
	 */
	private static function _replaceJsExpr($data, array &$jsExpressions){
		if(is_object($data)){
			le("Please convert to associative array first");
		}
		if(!is_array($data)){
			return $data;
		}
		if(isset($data['_expression'])){
			$magicKey = "____".count($jsExpressions)."_".count($jsExpressions);
			$jsExpressions[$magicKey] = $data['_expression'];
			return $magicKey;
		}
		foreach($data as $key => $value){
			if(is_array($data)){
				$data[$key] = static::_replaceJsExpr($value, $jsExpressions);
			}
		}
		return $data;
	}
	/**
	 * @param string $html
	 */
	public function validateChartHtml(string $html): void{
		if(strpos($html, '"tooltip": null') !== false){
			le('tooltip: null');
		}
		if(strpos($html, '"marker": null') !== false){
			le('marker": null');
		}
	}
	/**
	 * @return string
	 */
	public function getThemeName(): string{
		return $this->themeName;
	}
	/**
	 * Any theme to use instead of the default one
	 * Basically, copy everything below the `import Highcharts from '../parts/Globals.js`';` statement
	 * from example themes like the one at https://www.highcharts.com/demo/line-basic/dark-unica
	 * @param string|null $themeName
	 */
	public function setThemeName(string $themeName){
		$this->themeName = $themeName;
	}
	public function setHeight(?int $height){
		$this->getChart()->height = $height;
	}
	/**
	 * @return Chart
	 */
	public function getChart(): Chart{
		return $this->chart;
	}
	/**
	 * @param string $html
	 * @return string
	 */
	protected function addStyleTag(string $html): string{
		if($this->css){
			$html = $this->getCssStyleTag().$html;
		}
		return $html;
	}
	/**
	 * @return string
	 */
	protected function getCssStyleTag(): string{
		return "<style>
$this->css
</style>";
	}
	public function getCardBackgroundGradientColorCss(): string{
		return CssHelper::generateGradientBackground($this->cardBackgroundColor);
	}
	private function getCardBody(): string{
		if(!$this->cardBody && $this->url){
			$description = $this->getSubtitleAttribute();
			$icon = ($this->fontAwesome) ? FontAwesome::html($this->fontAwesome) : '';
			$title = $this->title->text;
			return "
                <h4 class=\"card-title\">
                    <a href=\"{{ $this->url }}\"  style=\"color: #3C4858; text-decoration: none;\">
                        <i class=\"{{ $icon }}\"></i> $title
                    </a>
                </h4>
                <p class=\"card-category\">$description</p>
";
		}
		return $this->cardBody;
	}
	public function getSubtitleAttribute(): string{
		return $this->subtitle->text;
	}
	public function getBackgroundColor(): string{
		return $this->chart->backgroundColor;
	}
	/**
	 * @param DBModel $obj
	 * @return string
	 */
	public function urlTagDiv(DBModel $obj): string{
		$url = $this->getId().".js";
		return $this->wrap($obj, "<script src=\"$url\" defer></script>");
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		if(!is_string($this->id)){
			return $this->id->scalar;
		}
		return $this->id;
	}
	/**
	 * @param mixed $id
	 */
	public function setId(string $id): void{
		$this->id = $id;
		$this->setRenderTo($id);
	}
	/**
	 * @param DBModel|BaseModel $obj
	 * @param string $scriptOrUrlTag
	 * @return string
	 */
	public function wrap($obj, string $scriptOrUrlTag): string{
		$renderToDiv = $this->renderToDiv();
		$heading = $this->heading($obj);
		return $this->addStyleTag("
<div style=\"padding-top: 1rem; padding-bottom: 1rem;\">
    $heading
    $renderToDiv
    $scriptOrUrlTag
</div>
");
	}
	/**
	 * @param BaseModel|DBModel $obj
	 * @return string
	 */
	protected function heading($obj = null): string{
		$headingId = $this->headingId();
		$heading = $this->getTitleAttribute();
		if($obj){
			//  Shorter version of title for use in table of contents
			$heading = trim(str_replace($obj->getTitleAttribute(), "", $heading));
		}
		return "<h2 id=\"$headingId\" style=\"visibility: hidden; height: 0;\">$heading</h2>";
	}
	/**
	 * @return string
	 */
	protected function headingId(): string{
		$headingId = $this->getId();
		$headingId = str_replace("-container", "", $headingId);
		return $headingId;
	}
	public function getTitleAttribute(): string{
		$t = $this->title->text;
		if(!$t){
			le("", $this);
		}
		return $t;
	}
	/**
	 * @param $obj
	 * @return string
	 */
	public function inlineWithHeading($obj): string{
		return $this->wrap($obj, $this->inlineScript());
	}
	public function setCardBody(string $string){
		$this->cardBody = $string;
	}
	public function getIcon(): string{
		return $this->fontAwesome;
	}
	/**
	 * @param string $footer
	 * @return static
	 */
	public function setFooter(string $footer): self{
		$this->footer = $footer;
		return $this;
	}
	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description): self{
		$this->setSubTitle($description);
		return $this;
	}
	/**
	 * @param int $millis
	 * @return static
	 */
	public function setXAxisMinIfNecessary(int $millis): HighchartConfig{
		$min = $this->getXAxisMin();
		if(!$min || $min > $millis){
			$this->setXAxisMin($millis);
		}
		return $this;
	}
	public function getXAxisMin(): ?int{
		return $this->getXAxis()->min;
	}
	/**
	 * @param int $millis
	 * @return static
	 */
	public function setXAxisMin(int $millis): HighchartConfig{
		$this->getXAxis()->min = $millis;
		return $this;
	}
	/**
	 * @return View
	 */
	public function getMaterialView(): View{
		return view('material-chart', ['chart' => $this]);
	}
	public function setTooltipShared(bool $val): void{
		$this->getTooltip()->shared = $val;
	}
	/** @noinspection PhpUnused */
	public function renderFullPageHtml(): string{
		$this->setDarkTheme();
		$div = $this->inlineNoHeading();
		return HtmlHelper::renderView(view('highcharts-js')).$div;
	}
	public function setDarkTheme(): void{
		$this->setThemeName(self::THEME_DARK);
		$this->setColorPalette(self::COLORS_FOR_DARK_BACKGROUND);
	}
	public function getDynamicContent(): string{
		return $this->getHtml(false);
	}
	public function getHtml(bool $includeJS = true): string{
		if($includeJS){
			$html = HighchartConfig::getHighchartsJsScriptTags().$this->inlineNoHeading();
		} else{
			$html = $this->inlineNoHeading();
		}
		return QMStr::removeEmptyLines($html);
	}
	/**
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getHighchartsJsScriptTags(): string{
		/** @noinspection PhpUnhandledExceptionInspection */
		$js = view('highcharts-js')->render();
		return $js;
	}
	/**
	 * @return string
	 */
	public function saveHtmlLocally(): string{
		FileHelper::writeHtmlFile("tmp/charts/".$this->getId(), $this->getHtmlPage());
		return FileHelper::getStaticUrlForFile($this->getId());
	}
	public function getHtmlPage(bool $inlineJs = false): string{
		$params = [];
		if($inlineJs){$params['inlineJs'] = true;}
		return $this->renderPage($params);
	}
	public function renderPage(array $params = []): string{
		return HtmlHelper::renderReportWithoutTailwind($this->getShowContent(), $this, 
		                                $this->getShowParams($params));
	}
	public function getShowContent(bool $inlineJs = false): string{
		return $this->getHtml(false);
	}
	public function getShowParams(array $params): array{
		$params['model'] = $this;
		return $params;
	}
	/**
	 * @param string $jsFunction
	 */
	public function setTooltipFormatter(string $jsFunction): void{
		$this->getTooltip()->setFormatter($jsFunction);
	}
	public function setTitleColor(string $color){
		$this->getHCTitle()->setColor($color);
	}
	public function setSubtitleColor(string $color){
		$this->getSubtitle()->setColor($color);
	}
	public function getSubtitle(): Subtitle{
		$this->subtitle = Subtitle::instantiateIfNecessary($this->subtitle);
		return $this->subtitle;
	}
	/**
	 * @param string|null $subTitle
	 * @return $this
	 */
	public function setSubtitle(?string $subTitle): HighchartConfig{
		$this->getSubtitle()->setText($subTitle);
		return $this;
	}
	public function getHtmlContent(): string{
		return $this->getHtml(false);
	}
	public function getCard(): QMCard{
		$c = new QMCard();
		$c->setTitle($this->getTitleAttribute());
		$c->id = $this->getId();
		$c->setContentAndHtmlContent($this->getShowContent());
		return $c;
	}
	public function setPositiveAndNegativeColorsByCategory(string $positive = QMColor::HEX_GOOGLE_GREEN,
	                                                       string $negative = QMColor::HEX_GOOGLE_RED,
	                                                       float  $avg = null){
		$values = [];
		$categories = $this->getXAxis()->getCategories();
		foreach($categories as $i => $category){
			$values[] = (float)$category;
		}
		if($avg === null){
			$avg = Stats::average($values);
		}
		foreach($this->getSeries() as $series){
			$data = $series->getData();
			foreach($data as $i => $datum){
				if(is_array($datum)){
					$y = $datum['y'];
				} else{
					$y = $datum;
				}
				$label = $categories[$i];
				if((float)$label > $avg){
					$color = $positive;
				} else{
					$color = $negative;
				}
				$series->data[$i] = [
					'y' => $y,
					'color' => $color,
				];
			}
		}
	}
	public function setPositiveAndNegativeColorsByY(string $positive = QMColor::HEX_GOOGLE_GREEN,
	                                                string $negative = QMColor::HEX_GOOGLE_RED, float $avg = null){
		foreach($this->getSeries() as $series){
			$data = $series->getData();
			if($avg === null){
				$avg = $series->getAverage();
			}
			foreach($data as $i => $datum){
				if(is_array($datum)){
					$y = $datum['y'];
				} else{
					$y = $datum;
				}
				if($y > $avg){
					$color = $positive;
				} else{
					$color = $negative;
				}
				$series->data[$i] = [
					'y' => $y,
					'color' => $color,
				];
			}
		}
	}
	public function getShowPageView(array $params = []): View{
		return HtmlHelper::getReportViewWithoutTailwind($this->getHtml(false), $this, $this->getShowParams($params));
	}
	public function getMenu(): QMMenu{
		return JournalMenu::instance();
	}
	public function getFontAwesome(): string{
		return FontAwesome::CHARTS;
	}
	/**
	 * @param string $fontAwesome
	 * @return static
	 */
	public function setFontAwesome(string $fontAwesome): HighchartConfig{
		$this->fontAwesome = $fontAwesome;
		return $this;
	}
	public function setPointSize(int $size){
		$this->getPlotOptions()->getSeries()->getMarker()->radius = $size;
	}
	public function deleteMarkerSettings(){
		$this->getPlotOptions()->getSeries()->marker = null;
	}
	public function deletePlotOptions(){
		$this->plotOptions = null;
	}
	public function getUrlSubPath(): string{
		return $this->getId();
	}
	/** @noinspection PhpUnused */
	public function getKeyWordString(): string{
		$keywords = $this->getKeyWords();
		return QMStr::generateKeyWordString($keywords);
	}
	public function getKeyWords(): array{
		if($s = $this->getSourceObject()){
			$keywords = $s->getKeyWords();
		} else{
			$keywords = QMStr::stringsToKeywords([
				                                     $this->getTitleAttribute(),
				                                     //$this->getSubtitleText(),
			                                     ]);
		}
		$keywords[] = QMStr::titleCaseFast($this->getType())." Chart";
		return $keywords;
	}
	/**
	 * @return BaseModel|DBModel|null
	 */
	public function getSourceObject(){
		$c = $this->getQmChart();
		if(!$c){
			return null;
		}
		return $c->getSourceObject();
	}
	/**
	 * @return QMChart|null
	 */
	public function getQmChart(): ?QMChart{
		return $this->qmChart;
	}
	/**
	 * @param QMChart $qmChart
	 */
	public function setQmChart(QMChart $qmChart): void{
		$this->qmChart = $qmChart;
	}
	public function getType(): string{
		return $this->getChart()->getType();
	}
	/**
	 * @param $model
	 * @throws NotEnoughDataException
	 */
	public function assertHasData($model){
		if(!$this->hasData()){
			throw new NotEnoughDataException($model, "No data for ".static::class);
		}
	}
	public function hasData(): bool{
		$series = $this->getSeries();
		if(!$series){
			return false;
		}
		$data = $series[0]->getData();
		if(!$data){
			return false;
		}
		return true;
	}
	/**
	 * @param array|string $key
	 * @return mixed
	 */
	public function getAttribute($key){
		return $this->$key;
	}
	public function getNameAttribute(): string{
		return $this->getTitleAttribute();
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function getImage(): string{
		return ImageUrls::CHARTS;
	}
	/**
	 * @param string $name
	 */
	protected function setFileName(string $name): void{
		$this->setExportFileName($name);
	}
	public function setExportFileName(string $name){
		$this->exporting->filename = $name;
	}
	protected function getHeight(): ?int{
		return $this->getChart()->height;
	}
}
