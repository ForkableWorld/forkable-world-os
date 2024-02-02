<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Buttons\Links\AboutUsButton;
use App\Charts\CorrelationCharts\PairsOverTimeLineChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Computers\ThisComputer;
use App\Exceptions\SecretException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Repos\ResponsesRepo;
use App\Slim\View\Request\QMRequest;
use App\Storage\S3\S3Helper;
use App\Storage\S3\S3Private;
use App\Storage\S3\S3Public;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
/** Class HighchartExport
 * @package App\Charts
 */
class HighchartExport {
	use LoggerTrait, HasClassName;
	// See https://github.com/highcharts/node-export-server
	//public const HIGHCHART_EXPORT_SERVER_URL = 'http://97.91.133.32:7801';
	private $chart;
	private $imageData;
	private $imageType;
	private $outputFileName;
	public $type;
	private $useHighStock;
	private $outputPath;
	/**
	 * @param float $scale To set the zoomFactor of the page rendered by PhantomJs. For example, if the chart.width
	 *     option in the chart configuration is set to 600 and the scale is set to 2, the output raster image will have
	 *     a pixel width of 1200. So this is a convenient way of increasing the resolution without decreasing the font
	 *     size and line widths in the chart. This is ignored if the -width parameter is set.
	 */
	private $scale;
	/**
	 * @param float $width Set the exact pixel width of the exported image or pdf. This overrides the -scale parameter.
	 */
	private $width;
	public const DEFAULT_IMAGE_FORMAT = self::PNG; // PNG can be embedded in PDF's and SVG export stopped working for some reason
	public const JPG = "jpg";
	public const LIFETIME = 14 * 86400;
	public const PNG = "png";
	public const SVG = "svg";
	//public static $HIGHCHART_EXPORT_SERVER_URL = 'https://highcharts-node-export-server-n66b6ronka-uc.a.run.app';
	public static $HIGHCHART_EXPORT_SERVER_URL = 'http://quantimodo2.asuscomm.com:7801';
	// http://quantimodo2.asuscomm.com:8082/job/highcharts-export-server-windows
	// http://quantimodo2.asuscomm.com:8082/job/highcharts-export-server-ubuntu
	//public static $HIGHCHART_EXPORT_SERVER_URL = 'http://192.168.1.175:7801';
	/**
	 * @var HighchartConfig
	 */
	private $highchartConfig;
	/**
	 * HighchartExport constructor.
	 * @param HighchartConfig|QMChart $chart
	 */
	public function __construct($chart){
		if($chart instanceof QMChart){
			$this->chart = $chart;
			$this->setHighchartConfig($chart->getHighchartConfig());
			$chart->validate();
		} else{
			$this->setHighchartConfig($chart);
		}
	}
	/**
	 * @param string $type
	 * @return array|bool
	 */
	public function generateImageDataAndUploadToS3(string $type): ?array{
		$this->logInfo(__FUNCTION__);
		$this->type = $type;
		$chart = $this->getChart();
		$s3BucketAndFilePathWithExtension = $chart->getS3BucketAndFilePath($type);
		$response = ['url' => $chart->getUrlForImage($type)];
		$data = null;
		try {
			$data = $this->exportLocally($type);
		} catch (\Throwable $e){
		    QMLog::error(__METHOD__.": ".$e->getMessage());
		}
		if(!$data){
			$data = $this->exportRemotely($type);
		}
		$this->validateExport($type, $data, $chart);
		if($type === self::SVG){
			//$response = HighchartExport::addQuantiModoLogoToSvg($response);
			$data = self::addQuantiModoLinkToSvg($data,
				$this->getUrl()); // Link instead of graphic brings size from 256kb to 13kb
		}
		if($this->isPublic()){ // Don't waste time on private charts because they're embedded in HTML anyway
			try {
				$response['result'] = S3Helper::putForS3BucketAndPath($s3BucketAndFilePathWithExtension, $data);
			} catch (SecretException | MimeTypeNotAllowed $e) {
				le($e);
			}
		}
		QMChart::setModifiedTime($s3BucketAndFilePathWithExtension, time());
		$response['data'] = $data;
		return $response;
	}
	/**
	 * @param string $data
	 * @param string $url
	 * @return string
	 */
	public static function addQuantiModoLinkToSvg(string $data, string $url): string{
		$offset = 295;
		if(stripos($data, 'height="400"') !== false){
			$offset = 395;
		} // Pair chart is taller for some reason
		$data = str_replace('</svg>', self::getQMLinkSvg($offset, $url) . '</svg>', $data);
		$data =
			str_replace(PairsOverTimeLineChart::DYNAMIC_LINE_COLOR, PairsOverTimeLineChart::EXPORT_LINE_COLOR, $data);
		return $data;
	}
	/**
	 * @param int $offset
	 * @param string $url
	 * @return string
	 */
	private static function getQMLinkSvg(int $offset, string $url): string{
		return '<a xlink:href="' . $url . '" target="_blank">
                    <text x="590" class="highcharts-credits" text-anchor="end" data-z-index="8"
                          style="cursor:pointer;color:#999999;font-size:9px;fill:#999999;" y="' . $offset . '">' .
			$url . '
                    </text>
                </a>';
	}
	/**
	 * @return QMChart
	 */
	public function getChart(): QMChart{
		return $this->chart;
	}
	/**
	 * @param array $params
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getUrl(array $params = []): string{
		$url = AboutUsButton::QM_INFO_URL;
		$sourceObject = $this->getChart()->getSourceObject();
		if($sourceObject){
			$url = $sourceObject->getUrl();
		}
		$url = htmlspecialchars($url);
		return $url;
	}
	public static function getServerUrl(): string{
		if($url = self::$HIGHCHART_EXPORT_SERVER_URL){
			return $url;
		}
		$url = "http://127.0.0.1:7801";
		if(self::urlWorks($url)){
			return static::$HIGHCHART_EXPORT_SERVER_URL = $url;
		}
		//        self::startHighchartsServer();
		//        if(self::urlWorks($url)){return static::$HIGHCHART_EXPORT_SERVER_URL = $url;}
		$url = "http://localhost:8889";
		if(self::urlWorks($url)){
			return static::$HIGHCHART_EXPORT_SERVER_URL = $url;
		}
		$url = "https://highcharts-node-export-server-n66b6ronka-uc.a.run.app:8080";
		if(self::urlWorks($url)){
			return static::$HIGHCHART_EXPORT_SERVER_URL = $url;
		}
		//        $dockerOutput = ServerHelper::dockerCommand("run -d --name highcharts -p 8889:8080 onsdigital/highcharts-export-node");
		//        if($dockerOutput['exit_status'] == 0 || stripos($dockerOutput['output'], "highcharts\" is already in use")){
		//            if(self::urlWorks($url)){return static::$HIGHCHART_EXPORT_SERVER_URL = $url;}
		//        }
		$remote = 'http://quantimodo2.asuscomm.com:7801';
		QMLog::error("Local highchart server not available so using $remote");
		return self::$HIGHCHART_EXPORT_SERVER_URL = $remote;
	}
	private static function getTestChartData(): string{
		return '{"infile":{"title": {"text": "Steep Chart"}, "xAxis": {"categories": ["Jan", "Feb", "Mar"]}, "series": [{"data": [29.9, 71.5, 106.4]}]}}';
	}
	/**
	 * @param $url
	 * @return bool
	 */
	private static function urlWorks($url): bool{
		$chartResponse = APIHelper::makePostRequest($url, self::getTestChartData());
		if(stripos((string)$chartResponse, "PNG") !== false){
			\App\Logging\ConsoleLog::info("Highcharts already running at $url");
			return true;
		}
		\App\Logging\ConsoleLog::info("Highcharts NOT running at $url");
		return false;
	}
	private function isPublic(): ?bool{
		return $this->getChart()->getSourceObject()->getIsPublic();
	}
	/**
	 * @param string $type
	 * @return string
	 */
	public function exportLocally(string $type): string{
		$this->setImageType($type);
		$constr = $this->getConstructor();
		$configPath = self::getConfigPath();
		$outputPath = $this->getOutputFilePath();
		$flags = $this->getScaleWidthFlags();
		$this->writeConfig();
		self::deleteOutputImageFile($this->getOutputFileName());
		$this->logInfo("Exporting locally as $type...
Config: $configPath
Output: $outputPath");
		$exportCommand =
			"highcharts-convert.js -infile " . $configPath . " -constr " . $constr . " -outfile " .
			$outputPath . " " . $flags;
		$output = self::execute($exportCommand);
		if(stripos($output, "error") !== false){
			$this->generateDebugHtml($output);
		}
		return $this->imageData = file_get_contents($outputPath);
	}
	public static function startHighchartsServer(){
		ThisComputer::exec("forever stopall");
		ThisComputer::exec("npm i phantomjs-prebuilt --unsafe-perm");
		ThisComputer::exec("export ACCEPT_HIGHCHARTS_LICENSE=\"YES\" && npm install highcharts-export-server --unsafe-perm");
		ThisComputer::exec("npm install forever --unsafe-perm");
		ThisComputer::exec("forever start --killSignal SIGINT node_modules/highcharts-export-server/bin/cli.js --enableServer 1 --tmpDir /var/www/tmp --logLevel 1");
	}
	/**
	 * @param array $response
	 * @return array
	 */
	public static function addQuantiModoLogoToSvg(array $response): array{
		$response['data'] = str_replace('</svg>', self::getQMLogoSvg() . '</svg>', $response['data']);
		return $response;
	}
	/**
	 * @return string
	 */
	public function exportRemotely(string $type): string{
		$this->type = $type;
		$url = self::getServerUrl();
		QMLog::error("Local export failed so using $url");
		$conf = $this->getExportableConfig();
		$json = json_encode(['infile' => $conf]);
		$data = APIHelper::makePostRequest($url, $json);
		return $data; 
	}
	public function getExportableConfig(): \stdClass{
		$title = $this->getTitleAttribute();
		$subtitle = $this->getSubtitle();
		$config = $this->getHighchartConfig();
		return $config->getExportableConfig($title, $subtitle);
	}
	/**
	 * @param string $type
	 * @param string $data
	 * @param QMChart $chart
	 */
	public function validateExport(string $type, string $data, QMChart $chart): void{
		if(stripos($data, "error generating")){
			throw new RuntimeException($data);
		}
		$length = strlen($data);
		if($length < 3000){
			$path = "broken/" . $chart->getFileName($type);
			try {
				S3Private::put($path, $data);
			} catch (SecretException | MimeTypeNotAllowed $e) {
				le($e);
			}
			$url = S3Private::url($path);
			throw new RuntimeException("Highchart export too small because length is only $length.
                Check it: $url
                Something might be wrong with config.  ");
		}
		if(!$data){
			$this->generateDebugHtml("$type export failed!");
		}
		if($type === self::SVG){
			if(!$chart->containsChartTitle($data)){
				$infile = $this->getExportableConfig();
				$this->generateDebugHtml("Chart does not contain title!  Infile title is " .
					json_encode($infile->title));
			}
		}
	}
	/**
	 * @return bool
	 */
	public static function shouldGenerate(): bool{
		$shouldGenerate = true;
		if(QMRequest::urlContains('/feed')){
			QMRequest::urlContains('/feed');
			QMLog::logicExceptionIfNotProductionApiRequest("We shouldn't be generating charts in feed request!");
			$shouldGenerate = false;
		}
		return $shouldGenerate;
	}
	/**
	 * @param string $errorMessage
	 */
	public function generateDebugHtml(string $errorMessage): void{
		$config = self::getConfigContents();
		$rendered = QMStr::prettyJsonEncode($config);
		$path = ResponsesRepo::saveFile("debug.html", "
            <div id=\"container\"></div>
            <script type=\"text / javascript\" defer>
                window.chart = $rendered;
            </script>
        ");
		$url = FileHelper::getStaticUrlForFile($path);
		le($errorMessage . "
            Export failed
            Debug at
            $path
            or
            $url
            ", $this);
	}
	/**
	 * @return string
	 */
	public function getImageData(): string{
		if($this->imageData){
			return $this->imageData;
		}
		$this->exportLocally($this->getImageType());
		return $this->imageData;
	}
	/**
	 * @return HighchartConfig
	 */
	public function getHighchartConfig(): HighchartConfig{
		$config = $this->highchartConfig;
		if(!$config){
			$chart = $this->getChart();
			$config = $chart->getHighchartConfig();
			$config->validate();
		}
		$config->validate();
		return $this->setHighchartConfig($config);
	}
	/**
	 * @param HighchartConfig|string|object $config
	 * @return HighchartConfig
	 */
	public function setHighchartConfig($config): HighchartConfig{
		$config = HighchartConfig::instantiateIfNecessary($config);
		$config->validate();
		return $this->highchartConfig = $config;
	}
	/**
	 * @param string $svg
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function addMetaDataToSvg(string $svg): string{
		$url = $this->getUrl();
		$metaData = '<metadata>
          <rdf:RDF
               xmlns:rdf = "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
               xmlns:rdfs = "http://www.w3.org/2000/01/rdf-schema#"
               xmlns:dc = "http://purl.org/dc/elements/1.1/" >
            <rdf:Description about="' . $url . '"
                 dc:title="' . $this->getTitleAttribute() . '"
                 dc:description="' . $this->getSubtitle() . '"
                 dc:publisher="Awesome FDA"
                 dc:date="' . TimeHelper::YYYYmmddd() . '"
                 dc:format="image/svg+xml"
                 dc:language="en" >
              <dc:creator>
                <rdf:Bag>
                  <rdf:li>Mike P. Sinn</rdf:li>
                </rdf:Bag>
              </dc:creator>
            </rdf:Description>
          </rdf:RDF>
        </metadata>';
		$svg = str_replace([
			'</desc>',
			'Created with Highstock 6.2.0',
		], [
			'</desc>' . $metaData,
			$this->getTitleAttribute(),
		], $svg);
		return $svg;
	}
	/**
	 * @return string
	 */
	private static function getQMLogoSvg(): string{
		/** @noinspection SpellCheckingInspection */
		return '<g>
          <path fill="#262425" d="M957.144,1169.048c-1.074,0.567-2.411,0.852-4.012,0.852c-2.067,0-3.722-0.666-4.964-1.996
            c-1.243-1.332-1.864-3.077-1.864-5.239c0-2.323,0.698-4.2,2.097-5.633c1.397-1.432,3.172-2.148,5.319-2.148
            c1.377,0,2.519,0.199,3.424,0.6v1.813c-1.04-0.581-2.188-0.873-3.445-0.873c-1.668,0-3.021,0.558-4.058,1.672
            c-1.037,1.114-1.556,2.604-1.556,4.468c0,1.771,0.485,3.181,1.454,4.23s2.241,1.578,3.815,1.578c1.459,0,2.721-0.326,3.789-0.976
            V1169.048z"/>
        <path fill="#262425" d="M964.771,1160.963c-0.291-0.225-0.709-0.336-1.256-0.336c-0.709,0-1.302,0.336-1.778,1.004
            c-0.477,0.668-0.714,1.582-0.714,2.735v5.29h-1.663v-10.376h1.663v2.139h0.04c0.236-0.73,0.597-1.3,1.084-1.708
            c0.486-0.408,1.029-0.611,1.63-0.611c0.433,0,0.764,0.046,0.993,0.141V1160.963z"/>
        <path fill="#262425" d="M970.392,1169.9c-1.533,0-2.757-0.483-3.672-1.454c-0.916-0.968-1.373-2.254-1.373-3.854
            c0-1.743,0.476-3.105,1.428-4.085c0.953-0.979,2.239-1.468,3.861-1.468c1.547,0,2.753,0.475,3.623,1.429
            c0.867,0.952,1.302,2.271,1.302,3.96c0,1.656-0.468,2.981-1.404,3.978S971.967,1169.9,970.392,1169.9z M970.514,1160.435
            c-1.067,0-1.911,0.364-2.532,1.09c-0.622,0.726-0.932,1.729-0.932,3.004c0,1.229,0.313,2.199,0.941,2.909
            c0.629,0.709,1.469,1.063,2.523,1.063c1.074,0,1.9-0.347,2.478-1.044c0.578-0.696,0.866-1.685,0.866-2.968
            c0-1.297-0.288-2.297-0.866-3C972.415,1160.789,971.588,1160.435,970.514,1160.435z"/>
        <path fill="#262425" d="M990.767,1159.281l-3.11,10.376h-1.723l-2.139-7.427c-0.081-0.284-0.135-0.605-0.161-0.964h-0.041
            c-0.021,0.243-0.091,0.558-0.214,0.944l-2.319,7.446h-1.661l-3.141-10.376H978l2.148,7.802c0.066,0.237,0.114,0.548,0.14,0.932
            h0.083c0.02-0.296,0.081-0.613,0.183-0.952l2.391-7.781h1.52l2.148,7.822c0.067,0.25,0.117,0.562,0.151,0.932h0.083
            c0.013-0.265,0.07-0.574,0.172-0.932l2.107-7.822H990.767z"/>
        <path fill="#262425" d="M1001.028,1169.657h-1.661v-1.764h-0.041c-0.77,1.337-1.959,2.007-3.566,2.007
            c-1.304,0-2.346-0.465-3.126-1.394c-0.779-0.929-1.17-2.193-1.17-3.794c0-1.716,0.433-3.091,1.297-4.124
            c0.865-1.034,2.016-1.55,3.455-1.55c1.425,0,2.462,0.56,3.111,1.681h0.041v-6.423h1.661V1169.657z M999.367,1164.965v-1.53
            c0-0.837-0.278-1.546-0.831-2.127c-0.555-0.581-1.257-0.873-2.108-0.873c-1.012,0-1.81,0.372-2.391,1.115
            c-0.582,0.744-0.871,1.771-0.871,3.081c0,1.195,0.279,2.14,0.836,2.832c0.556,0.691,1.304,1.037,2.244,1.037
            c0.924,0,1.676-0.335,2.254-1.003C999.078,1166.83,999.367,1165.985,999.367,1164.965z"/>
        <path fill="#262425" d="M1003.245,1169.282v-1.784c0.905,0.668,1.901,1.003,2.988,1.003c1.46,0,2.189-0.486,2.189-1.457
            c0-0.277-0.063-0.513-0.187-0.705c-0.125-0.193-0.294-0.364-0.507-0.512c-0.212-0.148-0.462-0.281-0.75-0.4
            c-0.287-0.118-0.597-0.242-0.927-0.37c-0.459-0.183-0.863-0.366-1.212-0.552c-0.347-0.187-0.637-0.396-0.87-0.629
            c-0.235-0.232-0.41-0.498-0.528-0.796c-0.119-0.295-0.178-0.645-0.178-1.044c0-0.485,0.112-0.916,0.335-1.291
            c0.222-0.375,0.52-0.688,0.893-0.941c0.37-0.253,0.794-0.444,1.27-0.572s0.968-0.192,1.474-0.192c0.898,0,1.702,0.155,2.412,0.466
            v1.682c-0.763-0.5-1.641-0.751-2.634-0.751c-0.312,0-0.592,0.036-0.842,0.107s-0.464,0.171-0.643,0.299
            c-0.178,0.129-0.317,0.282-0.415,0.461c-0.098,0.18-0.147,0.378-0.147,0.593c0,0.27,0.049,0.497,0.147,0.678
            c0.098,0.183,0.241,0.347,0.431,0.488c0.189,0.141,0.418,0.27,0.688,0.385c0.271,0.114,0.578,0.239,0.923,0.373
            c0.46,0.177,0.871,0.359,1.236,0.543c0.365,0.186,0.676,0.396,0.933,0.628c0.257,0.233,0.454,0.502,0.593,0.807
            c0.138,0.303,0.208,0.665,0.208,1.083c0,0.514-0.113,0.961-0.34,1.338c-0.227,0.379-0.529,0.692-0.906,0.942
            c-0.379,0.251-0.815,0.437-1.308,0.558c-0.492,0.121-1.01,0.183-1.55,0.183C1004.955,1169.9,1004.028,1169.693,1003.245,1169.282z"
            />
        <path fill="#262425" d="M1016.496,1169.9c-1.534,0-2.757-0.483-3.672-1.454c-0.916-0.968-1.373-2.254-1.373-3.854
            c0-1.743,0.476-3.105,1.428-4.085s2.239-1.468,3.86-1.468c1.546,0,2.754,0.475,3.622,1.429c0.869,0.952,1.302,2.271,1.302,3.96
            c0,1.656-0.468,2.981-1.403,3.978C1019.324,1169.401,1018.069,1169.9,1016.496,1169.9z M1016.617,1160.435
            c-1.066,0-1.911,0.364-2.533,1.09c-0.621,0.726-0.932,1.729-0.932,3.004c0,1.229,0.313,2.199,0.943,2.909
            c0.628,0.709,1.469,1.063,2.522,1.063c1.075,0,1.9-0.347,2.478-1.044c0.577-0.696,0.867-1.685,0.867-2.968
            c0-1.297-0.29-2.297-0.867-3C1018.517,1160.789,1017.691,1160.435,1016.617,1160.435z"/>
        <path fill="#262425" d="M1032.188,1169.657h-1.661v-1.643h-0.04c-0.69,1.258-1.758,1.886-3.203,1.886
            c-2.472,0-3.708-1.473-3.708-4.417v-6.202h1.651v5.938c0,2.19,0.837,3.282,2.513,3.282c0.811,0,1.477-0.299,2.001-0.896
            c0.524-0.6,0.786-1.38,0.786-2.347v-5.978h1.661V1169.657z"/>
        <path fill="#262425" d="M1040.434,1160.963c-0.291-0.225-0.71-0.336-1.257-0.336c-0.71,0-1.302,0.336-1.778,1.004
            c-0.476,0.668-0.714,1.582-0.714,2.735v5.29h-1.662v-10.376h1.662v2.139h0.04c0.237-0.73,0.599-1.3,1.084-1.708
            c0.487-0.408,1.03-0.611,1.631-0.611c0.433,0,0.764,0.046,0.994,0.141V1160.963z"/>
        <path fill="#262425" d="M1048.79,1169.18c-0.797,0.479-1.742,0.72-2.837,0.72c-1.478,0-2.673-0.481-3.582-1.444
            s-1.363-2.211-1.363-3.743c0-1.709,0.49-3.082,1.47-4.119c0.979-1.036,2.286-1.555,3.92-1.555c0.913,0,1.717,0.168,2.412,0.505
            v1.703c-0.77-0.541-1.593-0.812-2.471-0.812c-1.062,0-1.931,0.382-2.61,1.142c-0.678,0.759-1.018,1.757-1.018,2.992
            c0,1.217,0.32,2.176,0.957,2.88c0.638,0.701,1.495,1.052,2.568,1.052c0.906,0,1.756-0.301,2.553-0.901V1169.18z"/>
        <path fill="#262425" d="M1051.635,1156.647c-0.297,0-0.55-0.102-0.759-0.304c-0.209-0.203-0.313-0.459-0.313-0.77
            c0-0.313,0.104-0.57,0.313-0.776c0.209-0.204,0.462-0.31,0.759-0.31c0.305,0,0.563,0.105,0.776,0.31
            c0.213,0.206,0.319,0.464,0.319,0.776c0,0.296-0.106,0.55-0.319,0.759C1052.198,1156.542,1051.939,1156.647,1051.635,1156.647z
             M1052.446,1169.657h-1.662v-10.376h1.662V1169.657z"/>
        <path fill="#262425" d="M1063.903,1169.657h-1.661v-5.917c0-2.202-0.804-3.305-2.41-3.305c-0.833,0-1.519,0.313-2.063,0.938
            c-0.544,0.626-0.816,1.414-0.816,2.367v5.917h-1.662v-10.376h1.662v1.723h0.041c0.784-1.31,1.919-1.965,3.404-1.965
            c1.135,0,2.003,0.365,2.604,1.099c0.602,0.732,0.901,1.793,0.901,3.177V1169.657z"/>
        <path fill="#262425" d="M1075.371,1168.826c0,3.811-1.824,5.714-5.47,5.714c-1.285,0-2.406-0.242-3.366-0.729v-1.662
            c1.169,0.648,2.284,0.973,3.345,0.973c2.552,0,3.83-1.357,3.83-4.072v-1.136h-0.041c-0.79,1.325-1.98,1.987-3.566,1.987
            c-1.291,0-2.33-0.461-3.116-1.383c-0.788-0.923-1.18-2.161-1.18-3.714c0-1.763,0.424-3.165,1.271-4.205
            c0.848-1.041,2.007-1.56,3.48-1.56c1.398,0,2.436,0.56,3.111,1.681h0.041v-1.438h1.661V1168.826z M1073.709,1164.965v-1.53
            c0-0.824-0.279-1.53-0.836-2.118c-0.558-0.587-1.251-0.882-2.082-0.882c-1.027,0-1.831,0.373-2.412,1.121
            c-0.581,0.746-0.872,1.792-0.872,3.136c0,1.154,0.279,2.079,0.836,2.771c0.558,0.691,1.295,1.037,2.213,1.037
            c0.933,0,1.69-0.331,2.276-0.992C1073.417,1166.846,1073.709,1165.999,1073.709,1164.965z"/>
        <path fill="#F7941E" d="M956.431,1183.25c0,3.109-1.618,3.466-2.835,3.466c-1.269,0-2.958-0.373-2.958-3.607v-8.71h-2.68v8.814
            c0,4.978,2.973,6.023,5.468,6.023c2.593,0,5.686-1.082,5.686-6.24v-8.598h-2.681V1183.25z"/>
        <path fill="#FEBC11" d="M965.305,1186.82c-0.332,0-0.555-0.069-0.664-0.206c-0.079-0.1-0.21-0.367-0.21-1.044v-4.89h2.43v-2.418
            h-2.43v-3.06l-2.644,0.854v2.205h-1.659v2.418h1.659v5.163c0,2.177,1.096,3.375,3.083,3.375c0.689,0,1.263-0.123,1.708-0.368
            l0.283-0.158v-2.746l-0.879,0.666C965.795,1186.752,965.574,1186.82,965.305,1186.82z"/>
        <path fill="#95A43A" d="M973.039,1178.037c-1.655,0-2.996,0.517-3.984,1.532c-0.985,1.013-1.484,2.421-1.484,4.181
            c0,1.629,0.48,2.961,1.427,3.963c0.955,1.013,2.238,1.524,3.815,1.524c1.615,0,2.928-0.526,3.901-1.563
            c0.965-1.026,1.455-2.398,1.455-4.072c0-1.706-0.457-3.071-1.354-4.057C975.902,1178.545,974.632,1178.037,973.039,1178.037z
             M974.808,1186.067c-0.431,0.52-1.047,0.773-1.882,0.773c-0.823,0-1.456-0.265-1.937-0.805c-0.49-0.555-0.738-1.342-0.738-2.344
            c0-1.046,0.247-1.865,0.735-2.436c0.475-0.554,1.107-0.822,1.939-0.822c0.834,0,1.449,0.256,1.881,0.781
            c0.452,0.549,0.68,1.37,0.68,2.44C975.487,1184.713,975.259,1185.525,974.808,1186.067z"/>
        <path fill="#00A0AF" d="M985.116,1178.037c-1.119,0-2.064,0.333-2.826,0.993v-0.768h-2.642v15.188h2.642v-4.989
            c0.659,0.516,1.456,0.775,2.383,0.775c1.53,0,2.759-0.558,3.649-1.66c0.862-1.065,1.3-2.486,1.3-4.222
            c0-1.573-0.394-2.859-1.167-3.818C987.651,1178.542,986.528,1178.037,985.116,1178.037z M982.975,1181.186
            c0.441-0.506,1.016-0.752,1.755-0.752c0.681,0,1.201,0.234,1.591,0.712c0.412,0.508,0.621,1.239,0.621,2.18
            c0,1.15-0.231,2.046-0.688,2.661c-0.425,0.574-1,0.854-1.76,0.854c-0.645,0-1.159-0.217-1.574-0.66
            c-0.423-0.454-0.629-0.997-0.629-1.657v-1.347C982.29,1182.363,982.514,1181.713,982.975,1181.186z"/>
        <rect x="991.102" y="1178.262" fill="#95A43A" width="2.643" height="10.749"/>
        <path fill="#95A43A" d="M992.442,1173.805c-0.419,0-0.796,0.153-1.091,0.445c-0.298,0.293-0.457,0.679-0.457,1.111
            c0,0.437,0.159,0.82,0.459,1.11c0.295,0.286,0.671,0.437,1.088,0.437c0.424,0,0.807-0.155,1.105-0.448
            c0.302-0.298,0.46-0.676,0.46-1.099c0-0.435-0.16-0.82-0.463-1.115C993.247,1173.958,992.865,1173.805,992.442,1173.805z"/>
        <path fill="#F26522" d="M999.938,1178.037c-1.338,0-2.541,0.333-3.574,0.99l-0.254,0.163v3.066l0.902-0.763
            c0.842-0.713,1.756-1.06,2.794-1.06c0.64,0,1.291,0.184,1.417,1.438l-2.398,0.335c-3.022,0.421-3.657,2.239-3.657,3.692
            c0,0.994,0.332,1.813,0.988,2.426c0.645,0.604,1.531,0.911,2.631,0.911c0.968,0,1.789-0.277,2.459-0.822v0.597h2.643v-6.825
            C1003.89,1179.472,1002.523,1178.037,999.938,1178.037z M999.146,1186.84c-0.423,0-0.744-0.102-0.978-0.311
            c-0.219-0.193-0.321-0.43-0.321-0.743c0-0.455,0.106-0.759,0.313-0.903c0.291-0.203,0.756-0.352,1.382-0.438l1.704-0.235v0.332
            c0,0.687-0.197,1.231-0.602,1.668C1000.249,1186.633,999.759,1186.84,999.146,1186.84z"/>
        <path fill="#262425" d="M866.863,1111.006c0-7.446,3.827-14.048,9.719-18.139c1.185-11.367,11.314-20.247,23.635-20.247
            c0.378,0,0.756,0.009,1.132,0.025c2.985-3.036,6.848-5.29,11.206-6.398c2.019-0.513,4.099-0.77,6.182-0.77
            c3.121,0,6.101,0.571,8.831,1.606c0.145-0.056,0.292-0.109,0.438-0.162c0.007-0.002,0.013-0.004,0.019-0.006
            c0.149-0.053,0.298-0.104,0.449-0.153l0-0.001c2.479-0.83,5.146-1.284,7.926-1.284c1.955,0,3.856,0.224,5.674,0.646
            c2.134-0.936,4.509-1.458,7.015-1.458c6.434,0,12.02,3.442,14.815,8.485c5.569,0.419,10.267,3.765,12.375,8.412
            c5.751,1.291,10.279,6.327,10.279,12.319c0,0.574-0.042,1.14-0.12,1.693c2.15,2.087,3.704,4.769,4.371,7.694
            c0.668,2.919,0.441,6.016-0.641,8.808c-0.599,1.541-1.448,2.979-2.497,4.255c-0.754,0.938-1.813,1.596-2.565,2.501
            c-1.764,1.378-3.832,2.421-6.094,3.023c-0.034,7.594-6.546,13.739-14.575,13.739c-0.079,0-0.156-0.001-0.235-0.003
            c-2.043,2.003-4.908,3.249-8.082,3.249c-3.676,0-6.939-1.672-8.989-4.255c-3.698,7.863-12.025,13.343-21.706,13.343
            c-3.973,0-7.719-0.922-11.009-2.556c-3.175,4.848-8.846,8.075-15.313,8.075c-5.893,0-11.123-2.679-14.408-6.818
            c-9.295-0.089-16.803-7.247-16.803-16.066c0-2.944,0.838-5.705,2.299-8.077C868.077,1119.127,866.863,1115.202,866.863,1111.006z"
            />
        <path fill="#00A0AF" d="M934.149,1136.082c-0.649-1.534-1.579-2.912-2.76-4.095c-1.184-1.183-2.561-2.11-4.095-2.761
            c-1.59-0.672-3.277-1.014-5.014-1.014h-33.141c-5.306,0-9.624-4.316-9.624-9.622v-4.026h-3.259v4.026
            c0,1.738,0.342,3.425,1.013,5.014c0.649,1.534,1.578,2.912,2.761,4.096c1.182,1.182,2.559,2.111,4.094,2.76
            c1.589,0.673,3.276,1.014,5.015,1.014h33.141c5.306,0,9.623,4.317,9.623,9.624v48.304h3.259v-48.304
            C935.162,1139.359,934.821,1137.672,934.149,1136.082z"/>
        <path fill="#95A43A" d="M928.855,1110.288c-0.649-1.533-1.578-2.911-2.761-4.095c-1.183-1.182-2.561-2.111-4.094-2.761
            c-1.588-0.67-3.276-1.013-5.015-1.013h-10.41v3.259h10.41c5.306,0,9.624,4.318,9.624,9.626v74.097h3.258v-74.097
            C929.868,1113.565,929.527,1111.877,928.855,1110.288z"/>
        <path fill="#F7941E" d="M953.832,1089.741v45.561c0,5.307-4.317,9.623-9.624,9.623h-9.964c-1.738,0-3.425,0.342-5.015,1.014
            c-1.535,0.649-2.912,1.578-4.095,2.761c-1.182,1.183-2.111,2.56-2.76,4.095c-0.671,1.588-1.013,3.277-1.013,5.014v31.594h3.259
            v-31.594c0-5.305,4.317-9.623,9.624-9.623h9.964c1.738,0,3.427-0.34,5.014-1.013c1.535-0.65,2.913-1.578,4.095-2.76
            c1.182-1.184,2.111-2.561,2.761-4.096c0.671-1.59,1.013-3.276,1.013-5.015v-45.561H953.832z"/>
        <path fill="#FEBC11" d="M896.041,1107.125v23.876c0,5.307,4.318,9.624,9.625,9.624h0.855c1.739,0,3.426,0.34,5.015,1.014
            c1.534,0.648,2.912,1.577,4.094,2.759c1.183,1.185,2.111,2.562,2.761,4.096c0.671,1.59,1.012,3.277,1.012,5.015v35.893h-3.259
            v-35.893c0-5.308-4.317-9.625-9.624-9.625h-0.855c-1.74,0-3.428-0.34-5.016-1.011c-1.534-0.65-2.913-1.578-4.094-2.761
            c-1.183-1.184-2.111-2.56-2.76-4.095c-0.672-1.589-1.014-3.276-1.014-5.016v-23.876H896.041z"/>
        <path fill="#F26522" d="M971.842,1108.169v10.022c0,5.306-4.318,9.624-9.624,9.624l-12.22-0.007c-1.738,0-3.427,0.34-5.014,1.014
            c-1.536,0.649-2.913,1.577-4.095,2.759c-1.184,1.184-2.112,2.562-2.761,4.095c-0.671,1.591-1.013,3.277-1.013,5.015v48.71h3.258
            v-48.71c0-5.307,4.318-9.623,9.622-9.623l1.591,0.007h10.631c1.739,0,3.425-0.342,5.016-1.014c1.534-0.647,2.912-1.579,4.095-2.761
            c1.181-1.183,2.11-2.561,2.759-4.094c0.673-1.591,1.015-3.278,1.015-5.015v-10.022H971.842z"/>
        <path fill="#F7941E" d="M934.322,1089.16c3.413-2.115,8.213-3.433,13.533-3.433c10.357,0,18.752,4.994,18.752,11.157
            c0,5.888-7.666,10.71-17.382,11.126c-0.453,0.021-0.91,0.031-1.37,0.031c-1.169,0-2.313-0.064-3.422-0.186
            c-0.108,0.513-0.164,1.049-0.164,1.597c0,1.653,0.517,3.185,1.397,4.442c-3.046-0.884-5.321-3.584-5.57-6.853
            c-6.486-1.755-10.995-5.644-10.995-10.158C929.101,1093.887,931.089,1091.167,934.322,1089.16z"/>
        <g>
            <path fill="#00ADBA" d="M928.175,1083.382c-0.036-1.56-0.385-3.138-1.083-4.641c-0.043-0.091-0.086-0.181-0.131-0.27
                c0.531-0.371,1.057-0.757,1.573-1.154c-0.457-0.869-1.01-1.687-1.645-2.439c-0.568,0.324-1.125,0.658-1.673,1.007
                c-1.152-1.29-2.565-2.292-4.12-2.959c0.169-0.624,0.325-1.251,0.465-1.883c-0.916-0.366-1.872-0.636-2.847-0.803
                c-0.23,0.609-0.445,1.22-0.641,1.834c-1.669-0.238-3.406-0.129-5.098,0.373c-0.271-0.58-0.559-1.155-0.861-1.723
                c-0.945,0.305-1.855,0.709-2.714,1.206c0.215,0.607,0.446,1.208,0.692,1.801c-1.511,0.921-2.754,2.139-3.693,3.534
                c-0.586-0.268-1.181-0.521-1.782-0.763c-0.53,0.837-0.974,1.727-1.313,2.653c0.561,0.322,1.128,0.632,1.703,0.926
                c-0.546,1.606-0.748,3.326-0.563,5.043c-0.627,0.171-1.25,0.356-1.87,0.557c0.13,0.975,0.367,1.936,0.706,2.856
                c0.642-0.113,1.28-0.24,1.913-0.384c0.084,0.21,0.174,0.422,0.27,0.63c0.642,1.379,1.52,2.575,2.563,3.562
                c-0.373,0.529-0.735,1.068-1.084,1.617c0.734,0.658,1.538,1.236,2.396,1.722c0.422-0.496,0.832-1.001,1.228-1.515
                c1.508,0.806,3.18,1.277,4.903,1.381c0.054,0.639,0.123,1.28,0.209,1.92c0.991,0.033,1.987-0.039,2.966-0.217
                c0.005-0.647-0.004-1.291-0.032-1.935c0.888-0.185,1.768-0.473,2.626-0.871c0.736-0.343,1.421-0.749,2.052-1.209
                c0.456,0.453,0.925,0.895,1.405,1.326c0.788-0.605,1.508-1.298,2.148-2.058c-0.414-0.494-0.84-0.979-1.276-1.449
                c1.08-1.351,1.848-2.912,2.261-4.566c0.646,0.054,1.294,0.091,1.943,0.113c0.217-0.964,0.325-1.947,0.33-2.931
                C929.459,1083.563,928.818,1083.462,928.175,1083.382z M919.88,1091.486c-4.354,2.021-9.523,0.131-11.545-4.224
                c-2.021-4.354-0.131-9.521,4.225-11.545c4.354-2.021,9.522-0.131,11.544,4.225C926.125,1084.296,924.234,1089.465,919.88,1091.486
                z"/>
                <ellipse transform="matrix(-0.421 -0.907 0.907 -0.421 319.1101 2371.1787)" fill="#00ADBA" cx="916.315" cy="1083.746" rx="3.757" ry="3.802"/>
        </g>
        <polygon fill="#FAA21B" points="884.818,1136.247 883.616,1135.045 882.004,1139.016 880.392,1142.987 884.362,1141.375
            888.333,1139.762 887.132,1138.562 889.847,1135.846 887.532,1133.533 	"/>
        <path fill="#008FAF" d="M902.265,1114.999c0,0-3.119,4.555-3.119,6.277c0,1.722,1.396,3.12,3.119,3.12
            c1.723,0,3.12-1.398,3.12-3.12C905.384,1119.553,902.265,1114.999,902.265,1114.999z"/>
        <path fill="#FEBC11" d="M981.738,1108.961c0.019-0.589-0.023-1.177-0.127-1.755c-0.386,0-0.772,0.007-1.156,0.024
            c-0.212-1.026-0.644-1.964-1.239-2.768c0.271-0.271,0.535-0.552,0.793-0.839c-0.362-0.463-0.772-0.888-1.226-1.265
            c-0.297,0.248-0.586,0.504-0.869,0.764c-0.794-0.626-1.722-1.083-2.727-1.325c0.031-0.382,0.054-0.769,0.067-1.154
            c-0.576-0.123-1.165-0.182-1.753-0.178c-0.068,0.379-0.126,0.761-0.175,1.144c-0.136,0.003-0.272,0.011-0.409,0.022
            c-0.909,0.081-1.761,0.331-2.531,0.716c-0.221-0.314-0.451-0.623-0.689-0.929c-0.52,0.276-1.01,0.61-1.458,0.988
            c0.192,0.336,0.393,0.666,0.602,0.989c-0.76,0.678-1.368,1.517-1.778,2.455c-0.372-0.099-0.746-0.188-1.125-0.268
            c-0.221,0.545-0.384,1.114-0.483,1.696c0.363,0.133,0.729,0.255,1.096,0.37c-0.076,0.531-0.093,1.081-0.045,1.638
            c0.043,0.479,0.133,0.942,0.264,1.387c-0.348,0.163-0.694,0.335-1.034,0.518c0.18,0.56,0.424,1.1,0.72,1.608
            c0.363-0.13,0.723-0.271,1.078-0.421c0.54,0.873,1.262,1.614,2.11,2.176c-0.161,0.351-0.314,0.702-0.459,1.062
            c0.498,0.312,1.03,0.569,1.584,0.77c0.194-0.334,0.38-0.672,0.557-1.014c0.887,0.293,1.846,0.414,2.836,0.327
            c0.06-0.006,0.12-0.012,0.18-0.019c0.1,0.371,0.21,0.741,0.329,1.108c0.583-0.081,1.158-0.224,1.71-0.429
            c-0.065-0.381-0.141-0.759-0.226-1.133c0.963-0.383,1.814-0.971,2.508-1.704c0.317,0.22,0.638,0.434,0.965,0.637
            c0.395-0.438,0.743-0.914,1.034-1.425c-0.296-0.25-0.596-0.493-0.902-0.725c0.476-0.883,0.767-1.873,0.829-2.916
            C980.973,1109.061,981.355,1109.015,981.738,1108.961z"/>
        <path fill="#F7941E" d="M911.345,1121.656c2.902,2.862,7.571,2.83,10.433-0.071c2.86-2.9,2.828-7.569-0.073-10.432
            c-2.9-2.86-7.571-2.829-10.431,0.071C908.414,1114.125,908.445,1118.795,911.345,1121.656z M912.343,1120.886l0.976-0.872
            l1.9-1.701c0.365,0.25,0.784,0.383,1.208,0.402l0.5,2.499l0.257,1.285C915.471,1122.684,913.692,1122.148,912.343,1120.886z
             M920.891,1120.709c-0.757,0.766-1.663,1.291-2.628,1.575l-0.256-1.284l-0.5-2.501c0.24-0.114,0.466-0.271,0.665-0.472
            c0.147-0.146,0.268-0.311,0.367-0.482l2.455,0.691l1.26,0.357C921.962,1119.363,921.507,1120.085,920.891,1120.709z
             M922.552,1117.534l-1.259-0.355l-2.456-0.692c0.014-0.402-0.076-0.806-0.27-1.169l1.901-1.701l0.976-0.873
            C922.488,1114.142,922.859,1115.888,922.552,1117.534z M920.708,1111.922l-0.976,0.874l-1.9,1.7
            c-0.365-0.251-0.784-0.384-1.208-0.402l-0.501-2.501l-0.256-1.283C917.581,1110.125,919.36,1110.662,920.708,1111.922z
             M912.161,1112.099c0.756-0.767,1.663-1.291,2.627-1.573l0.257,1.281l0.5,2.503c-0.241,0.113-0.467,0.27-0.665,0.47
            c-0.147,0.149-0.27,0.313-0.367,0.485l-2.455-0.693l-1.261-0.355C911.092,1113.447,911.546,1112.724,912.161,1112.099z
             M911.76,1115.631l2.455,0.693c-0.013,0.401,0.077,0.805,0.27,1.168l-1.901,1.701l-0.976,0.874c-1.044-1.4-1.415-3.146-1.108-4.792
            L911.76,1115.631z"/>
        <g>
            <polygon fill="#F6891F" points="946.341,1119.447 940.913,1120.567 942.015,1122.278 937.786,1125 939.91,1128.297
                944.138,1125.574 945.24,1127.285 948.505,1122.805 951.769,1118.328 		"/>
                <rect x="935.031" y="1126.555" transform="matrix(-0.5414 -0.8407 0.8407 -0.5414 495.9257 2526.281)" fill="#F6891F" width="3.759" height="2.681"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="898.427,1107.502 901.42,1105.25 903.673,1108.244 902.782,1108.915 901.674,1107.443
                901.224,1110.631 900.119,1110.476 900.569,1107.288 899.098,1108.394 		"/>
            <polygon fill="#AAD9B5" points="902.475,1103.932 901.759,1103.543 902.878,1101.481 901.592,1101.862 901.361,1101.083
                903.979,1100.307 904.754,1102.926 903.975,1103.157 903.594,1101.87 		"/>
        </g>
        <polygon fill="#F26522" points="970.511,1081.737 967.34,1081.737 967.34,1078.566 965.134,1078.566 965.134,1081.737
            961.962,1081.737 961.962,1083.944 965.134,1083.944 965.134,1087.116 967.34,1087.116 967.34,1083.944 970.511,1083.944 	"/>
        <polygon fill="#F5EB00" points="882.787,1106.351 879.409,1106.351 879.409,1102.972 877.059,1102.972 877.059,1106.351
            873.681,1106.351 873.681,1108.7 877.059,1108.7 877.059,1112.077 879.409,1112.077 879.409,1108.7 882.787,1108.7 	"/>
        <polygon fill="#8DC63F" points="954.562,1075.148 956.341,1072.938 949.457,1070.97 942.572,1069.003 945.96,1075.311
            949.349,1081.62 951.129,1079.408 956.126,1083.435 959.558,1079.173 	"/>
        <polygon fill="#4EA0B4" points="934.099,1076.12 936.016,1077.412 932.146,1083.145 932.794,1083.583 936.662,1077.848
            938.578,1079.141 938.767,1076.447 938.955,1073.75 	"/>
        <path fill="#00A0AF" d="M921.901,1135.449c-1.461-1.459-3.828-1.459-5.289,0c-1.459,1.461-1.459,3.828,0,5.288
            c1.46,1.461,3.827,1.461,5.289,0C923.361,1139.277,923.361,1136.909,921.901,1135.449z M920.243,1139.079
            c-0.545,0.543-1.427,0.543-1.971,0c-0.544-0.544-0.544-1.427,0-1.972c0.544-0.543,1.426-0.543,1.971,0
            C920.786,1137.652,920.786,1138.534,920.243,1139.079z"/>
        <polygon fill="#79AD36" points="894.098,1149.671 894.975,1147.026 902.89,1149.647 903.187,1148.753 895.271,1146.132
            896.146,1143.487 892.955,1144.147 889.767,1144.806 	"/>
        <polygon fill="#17AEB2" points="934.778,1118.448 935.659,1118.4 935.5,1115.429 938.582,1115.352 934.798,1109.146
            931.314,1115.528 934.619,1115.449 	"/>
        <g>
            <path fill="#BFE2CE" d="M897.107,1092.588l-4.325-0.72l0.72-4.323c0.072-0.435-0.221-0.846-0.655-0.916
                c-0.434-0.074-0.845,0.221-0.917,0.655l-0.72,4.322l-4.324-0.719c-0.435-0.071-0.845,0.221-0.917,0.655
                c-0.072,0.434,0.221,0.844,0.655,0.917l4.324,0.72l-0.72,4.322c-0.071,0.435,0.222,0.847,0.657,0.918
                c0.433,0.072,0.844-0.221,0.917-0.655l0.719-4.324l4.324,0.72c0.434,0.074,0.845-0.22,0.917-0.654
                C897.833,1093.073,897.541,1092.66,897.107,1092.588z"/>
            <path fill="#95A43A" d="M901.212,1092.517c0.54-0.085,1.078-0.188,1.614-0.3c-0.024-0.864-0.15-1.726-0.376-2.563
                c-0.549,0.042-1.093,0.099-1.636,0.171c-0.299-0.994-0.764-1.943-1.391-2.804c0.386-0.385,0.764-0.783,1.131-1.192
                c-0.529-0.686-1.141-1.307-1.815-1.849c-0.417,0.357-0.826,0.724-1.222,1.1c-0.86-0.652-1.798-1.135-2.771-1.449
                c0.085-0.541,0.157-1.084,0.214-1.628c-0.832-0.246-1.692-0.392-2.555-0.431c-0.129,0.532-0.243,1.068-0.342,1.607
                c-1.049-0.023-2.096,0.132-3.095,0.457c-0.247-0.486-0.508-0.969-0.783-1.444c-0.814,0.289-1.597,0.675-2.323,1.149
                c0.209,0.509,0.433,1.01,0.669,1.503c-0.771,0.536-1.475,1.196-2.077,1.981c-0.054,0.07-0.105,0.14-0.157,0.211
                c-0.485-0.249-0.98-0.486-1.48-0.708c-0.488,0.712-0.896,1.484-1.205,2.294c0.468,0.288,0.943,0.563,1.424,0.823
                c-0.35,1.003-0.52,2.047-0.519,3.086c-0.54,0.085-1.079,0.185-1.615,0.3c0.024,0.864,0.151,1.727,0.377,2.561
                c0.547-0.041,1.093-0.1,1.636-0.169c0.299,0.993,0.764,1.942,1.391,2.801c-0.387,0.387-0.764,0.785-1.13,1.193
                c0.528,0.687,1.14,1.308,1.813,1.849c0.418-0.356,0.826-0.724,1.222-1.1c0.861,0.653,1.798,1.135,2.772,1.451
                c-0.086,0.539-0.157,1.083-0.214,1.629c0.831,0.244,1.691,0.389,2.555,0.431c0.13-0.533,0.243-1.071,0.341-1.608
                c1.049,0.021,2.097-0.133,3.097-0.457c0.247,0.486,0.508,0.968,0.782,1.443c0.815-0.29,1.596-0.675,2.322-1.15
                c-0.21-0.507-0.432-1.009-0.669-1.502c0.772-0.536,1.475-1.198,2.078-1.98c0.053-0.071,0.105-0.141,0.156-0.212
                c0.487,0.25,0.981,0.485,1.482,0.71c0.489-0.713,0.895-1.486,1.203-2.297c-0.467-0.288-0.942-0.562-1.424-0.821
                C901.044,1094.599,901.213,1093.555,901.212,1092.517z"/>
            <g>
                <path fill="#A9BF38" d="M890.914,1098.245c3.16,0.527,6.146-1.608,6.672-4.769c0.526-3.157-1.608-6.147-4.768-6.672
                    c-3.159-0.526-6.147,1.609-6.674,4.769C885.62,1094.732,887.754,1097.719,890.914,1098.245z M892.566,1088.313
                    c2.327,0.387,3.899,2.587,3.512,4.914c-0.387,2.324-2.587,3.896-4.914,3.509c-2.326-0.386-3.898-2.586-3.512-4.912
                    S890.24,1087.926,892.566,1088.313z"/>
                <circle fill="#A9BF38" cx="891.865" cy="1092.524" r="2.161"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M977.022,1086.324c-3.273-0.017-5.939,2.626-5.954,5.897c-0.016,3.274,2.625,5.939,5.899,5.955
                c3.273,0.015,5.938-2.627,5.953-5.898C982.936,1089.003,980.294,1086.339,977.022,1086.324z M976.973,1096.614
                c-2.409-0.012-4.354-1.974-4.343-4.385c0.011-2.409,1.974-4.354,4.384-4.343c2.41,0.011,4.354,1.975,4.343,4.385
                C981.346,1094.68,979.383,1096.625,976.973,1096.614z"/>
            <circle fill="#CFB52B" cx="976.994" cy="1092.25" r="2.209"/>
        </g>
        <circle fill="#8DC63F" cx="886.208" cy="1120.057" r="3.026"/>
        <g>
            <path fill="#FEBC11" d="M872.865,1132.118c-0.486-0.34-0.77-0.768-0.853-1.282c-0.081-0.516,0.06-1.034,0.423-1.555
                c0.396-0.569,0.868-0.911,1.417-1.023c0.549-0.113,1.087,0.014,1.615,0.383c0.504,0.352,0.794,0.779,0.869,1.278
                c0.074,0.499-0.081,1.022-0.461,1.57c-0.394,0.563-0.862,0.9-1.407,1.011C873.923,1132.61,873.389,1132.483,872.865,1132.118z
                 M880.272,1132.132l-9.219,3.73l-1.163-0.811l9.209-3.737L880.272,1132.132z M874.854,1129.458
                c-0.464-0.323-0.922-0.162-1.37,0.484c-0.427,0.608-0.419,1.068,0.023,1.377c0.452,0.313,0.897,0.154,1.341-0.481
                C875.281,1130.216,875.282,1129.757,874.854,1129.458z M874.705,1138.552c-0.487-0.339-0.771-0.767-0.853-1.282
                s0.058-1.034,0.422-1.555c0.397-0.569,0.869-0.911,1.418-1.022c0.549-0.114,1.087,0.014,1.614,0.381
                c0.509,0.354,0.8,0.78,0.875,1.274c0.073,0.498-0.079,1.018-0.461,1.566c-0.391,0.561-0.862,0.899-1.41,1.014
                C875.764,1139.042,875.229,1138.916,874.705,1138.552z M876.679,1135.883c-0.46-0.322-0.916-0.16-1.366,0.485
                c-0.425,0.608-0.415,1.069,0.034,1.383c0.451,0.315,0.898,0.156,1.342-0.48c0.205-0.296,0.305-0.566,0.297-0.813
                C876.978,1136.211,876.875,1136.019,876.679,1135.883z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M965.525,1122l0.649,0.987l-0.664,0.436l-0.631-0.958c-0.616,0.401-1.281,0.64-1.993,0.711l-0.827-1.259
                c0.27,0.027,0.618-0.013,1.046-0.122c0.429-0.106,0.786-0.242,1.069-0.403l-1.087-1.653c-0.883,0.203-1.555,0.236-2.017,0.098
                c-0.46-0.137-0.837-0.428-1.127-0.869c-0.313-0.475-0.412-0.99-0.295-1.547c0.117-0.557,0.433-1.044,0.952-1.462l-0.556-0.847
                l0.664-0.437l0.544,0.828c0.658-0.391,1.203-0.604,1.633-0.634l0.808,1.228c-0.594,0.021-1.175,0.178-1.742,0.47l1.133,1.722
                c0.821-0.196,1.477-0.236,1.967-0.119c0.49,0.119,0.877,0.396,1.166,0.832c0.329,0.505,0.435,1.015,0.317,1.533
                C966.413,1121.053,966.077,1121.543,965.525,1122z M962.246,1118.461l-0.948-1.439c-0.371,0.353-0.437,0.709-0.199,1.074
                C961.304,1118.408,961.687,1118.529,962.246,1118.461z M963.919,1119.561l0.906,1.377c0.39-0.351,0.464-0.711,0.223-1.078
                C964.852,1119.563,964.477,1119.464,963.919,1119.561z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M953.848,1101.663l-1.456-0.61l-1.081,2.581l-2.437-1.021l1.081-2.581l-5.29-2.218l0.714-1.7l8.384-5.719
                l2.632,1.103l-3.206,7.651l1.456,0.608L953.848,1101.663z M952.872,1093.166l-0.05-0.021c-0.165,0.17-0.45,0.422-0.854,0.756
                l-4.285,2.94l3.071,1.287l1.599-3.816C952.495,1093.971,952.667,1093.59,952.872,1093.166z"/>
        </g>
    </g>
    <g>
        <path fill="#00A0AF" d="M987.971,863.688c-4.165,0.51-7.139,4.313-6.629,8.479c0.486,3.964,3.986,6.896,7.971,6.673l0,0
            c0.17-0.008,0.339-0.023,0.507-0.045c0.259-0.03,0.521-0.076,0.777-0.134l0.331-0.076l-0.208-1.701l-0.408,0.101
            c-0.232,0.054-0.467,0.101-0.696,0.127c-3.252,0.396-6.194-1.94-6.588-5.15c-0.396-3.236,1.915-6.19,5.15-6.589
            c0.237-0.027,0.474-0.042,0.707-0.042l0.421-0.003l-0.207-1.7l-0.339,0.006C988.498,863.637,988.236,863.655,987.971,863.688z"/>
        <path fill="#00A0AF" d="M991.255,880.501l-0.451,0.095c-0.244,0.05-0.498,0.092-0.754,0.124c-0.21,0.024-0.422,0.045-0.632,0.057
            c-4.975,0.276-9.345-3.386-9.952-8.335c-0.636-5.199,3.078-9.946,8.276-10.584c0.213-0.023,0.428-0.042,0.642-0.056l0.58-0.026
            l-0.286-2.335l-0.435,0.022c-0.264,0.014-0.527,0.035-0.784,0.069c-6.482,0.792-11.11,6.711-10.317,13.193
            c0.754,6.173,6.204,10.734,12.406,10.389c0,0.002,0,0,0,0c0.261-0.015,0.526-0.038,0.788-0.069c0.267-0.032,0.542-0.077,0.819-0.13
            l0.388-0.073L991.255,880.501z"/>
    </g>
    <path fill="#262425" d="M999.173,874.221c-0.291-0.225-0.71-0.335-1.256-0.335c-0.71,0-1.305,0.335-1.781,1.005
        c-0.477,0.667-0.714,1.581-0.714,2.737v5.293h-1.665v-10.383h1.665v2.139h0.042c0.236-0.729,0.598-1.3,1.084-1.708
        c0.486-0.408,1.031-0.612,1.633-0.612c0.431,0,0.763,0.045,0.993,0.141V874.221z"/>
    <path fill="#262425" d="M1004.798,883.163c-1.534,0-2.759-0.483-3.675-1.456c-0.916-0.968-1.374-2.253-1.374-3.856
        c0-1.744,0.477-3.107,1.43-4.085c0.952-0.982,2.24-1.471,3.862-1.471c1.548,0,2.756,0.476,3.625,1.429
        c0.87,0.953,1.305,2.274,1.305,3.966c0,1.654-0.47,2.982-1.405,3.979C1007.629,882.665,1006.374,883.163,1004.798,883.163z
         M1004.919,873.693c-1.068,0-1.913,0.363-2.535,1.091c-0.623,0.727-0.932,1.727-0.932,3.006c0,1.231,0.313,2.201,0.943,2.909
        c0.626,0.711,1.47,1.063,2.523,1.063c1.075,0,1.901-0.345,2.48-1.043c0.577-0.697,0.866-1.688,0.866-2.971
        c0-1.297-0.289-2.297-0.866-3.002C1006.82,874.045,1005.994,873.693,1004.919,873.693z"/>
    <path fill="#262425" d="M1025.186,872.538l-3.112,10.383h-1.723l-2.14-7.433c-0.081-0.284-0.134-0.604-0.162-0.965h-0.04
        c-0.021,0.243-0.092,0.56-0.213,0.945l-2.322,7.452h-1.662l-3.144-10.383h1.744l2.15,7.809c0.066,0.234,0.114,0.544,0.141,0.933
        h0.083c0.018-0.299,0.081-0.617,0.181-0.953l2.394-7.788h1.521l2.148,7.827c0.067,0.25,0.119,0.56,0.152,0.935h0.082
        c0.013-0.266,0.07-0.575,0.173-0.935l2.108-7.827H1025.186z"/>
    <path fill="#262425" d="M1035.458,882.921h-1.665v-1.766h-0.041c-0.771,1.341-1.96,2.008-3.569,2.008
        c-1.306,0-2.347-0.463-3.128-1.394c-0.781-0.93-1.171-2.196-1.171-3.797c0-1.717,0.433-3.094,1.298-4.125
        c0.864-1.035,2.017-1.553,3.458-1.553c1.426,0,2.463,0.561,3.112,1.683h0.041v-6.428h1.665V882.921z M1033.793,878.228v-1.532
        c0-0.839-0.277-1.548-0.832-2.129c-0.553-0.58-1.257-0.873-2.109-0.873c-1.014,0-1.811,0.371-2.393,1.117
        c-0.581,0.743-0.872,1.769-0.872,3.082c0,1.195,0.279,2.138,0.837,2.833c0.557,0.692,1.306,1.037,2.246,1.037
        c0.926,0,1.678-0.333,2.256-1.001C1033.504,880.091,1033.793,879.248,1033.793,878.228z"/>
    <path fill="#95A43A" d="M1037.675,882.545v-1.783c0.904,0.668,1.902,1.001,2.99,1.001c1.461,0,2.191-0.483,2.191-1.458
        c0-0.277-0.063-0.512-0.187-0.704c-0.126-0.193-0.295-0.363-0.507-0.513c-0.213-0.148-0.465-0.283-0.75-0.399
        c-0.289-0.119-0.596-0.242-0.928-0.371c-0.459-0.183-0.863-0.366-1.212-0.553c-0.348-0.188-0.638-0.395-0.873-0.628
        c-0.233-0.232-0.408-0.501-0.526-0.797c-0.119-0.299-0.178-0.645-0.178-1.045c0-0.484,0.112-0.916,0.335-1.294
        c0.222-0.373,0.521-0.689,0.892-0.942c0.372-0.253,0.797-0.443,1.272-0.571c0.477-0.129,0.969-0.192,1.477-0.192
        c0.898,0,1.703,0.154,2.413,0.465v1.684c-0.763-0.501-1.643-0.75-2.636-0.75c-0.311,0-0.591,0.038-0.84,0.107
        c-0.251,0.068-0.466,0.171-0.645,0.299c-0.179,0.126-0.318,0.282-0.417,0.46c-0.097,0.181-0.146,0.378-0.146,0.595
        c0,0.271,0.049,0.495,0.146,0.676c0.099,0.185,0.241,0.347,0.432,0.489c0.189,0.143,0.418,0.27,0.688,0.384
        c0.271,0.117,0.578,0.241,0.924,0.379c0.459,0.172,0.872,0.356,1.236,0.54c0.365,0.186,0.674,0.396,0.933,0.629
        c0.257,0.232,0.455,0.502,0.595,0.807c0.137,0.304,0.207,0.666,0.207,1.084c0,0.515-0.113,0.96-0.34,1.34
        c-0.227,0.378-0.529,0.692-0.908,0.943c-0.378,0.249-0.813,0.436-1.308,0.558c-0.493,0.121-1.01,0.181-1.551,0.181
        C1039.386,883.163,1038.458,882.957,1037.675,882.545z"/>
    <path fill="#262425" d="M1050.935,883.163c-1.535,0-2.759-0.483-3.676-1.456c-0.917-0.968-1.374-2.253-1.374-3.856
        c0-1.744,0.478-3.107,1.43-4.085c0.953-0.982,2.241-1.471,3.863-1.471c1.548,0,2.755,0.476,3.624,1.429s1.304,2.274,1.304,3.966
        c0,1.654-0.467,2.982-1.404,3.979C1053.766,882.665,1052.51,883.163,1050.935,883.163z M1051.056,873.693
        c-1.068,0-1.913,0.363-2.533,1.091c-0.623,0.727-0.935,1.727-0.935,3.006c0,1.231,0.314,2.201,0.943,2.909
        c0.629,0.711,1.471,1.063,2.525,1.063c1.075,0,1.901-0.345,2.479-1.043c0.579-0.697,0.868-1.688,0.868-2.971
        c0-1.297-0.289-2.297-0.868-3.002C1052.957,874.045,1052.131,873.693,1051.056,873.693z"/>
    <path fill="#262425" d="M1066.639,882.921h-1.663v-1.642h-0.041c-0.689,1.256-1.757,1.884-3.204,1.884
        c-2.475,0-3.711-1.473-3.711-4.421v-6.204h1.653v5.942c0,2.19,0.839,3.282,2.516,3.282c0.811,0,1.477-0.298,2.003-0.894
        c0.522-0.599,0.785-1.383,0.785-2.349v-5.982h1.663V882.921z"/>
    <path fill="#262425" d="M1074.89,874.221c-0.29-0.225-0.71-0.335-1.256-0.335c-0.71,0-1.305,0.335-1.78,1.005
        c-0.478,0.667-0.715,1.581-0.715,2.737v5.293h-1.662v-10.383h1.662v2.139h0.041c0.237-0.729,0.598-1.3,1.085-1.708
        c0.486-0.408,1.03-0.612,1.632-0.612c0.433,0,0.764,0.045,0.993,0.141V874.221z"/>
    <path fill="#262425" d="M1083.253,882.445c-0.798,0.479-1.744,0.718-2.838,0.718c-1.48,0-2.675-0.481-3.585-1.445
        c-0.91-0.963-1.364-2.211-1.364-3.745c0-1.71,0.49-3.085,1.471-4.122c0.979-1.037,2.286-1.556,3.922-1.556
        c0.913,0,1.718,0.167,2.414,0.507v1.703c-0.771-0.542-1.594-0.812-2.474-0.812c-1.062,0-1.933,0.381-2.611,1.139
        c-0.68,0.763-1.018,1.761-1.018,2.999c0,1.217,0.32,2.177,0.957,2.878c0.64,0.704,1.497,1.054,2.571,1.054
        c0.905,0,1.756-0.298,2.555-0.901V882.445z"/>
    <path fill="#262425" d="M1086.1,869.901c-0.297,0-0.55-0.101-0.759-0.304c-0.21-0.202-0.315-0.459-0.315-0.771
        c0-0.311,0.104-0.569,0.315-0.773c0.208-0.207,0.462-0.311,0.759-0.311c0.306,0,0.563,0.104,0.777,0.311
        c0.213,0.204,0.319,0.463,0.319,0.773c0,0.299-0.105,0.551-0.319,0.762C1086.663,869.798,1086.405,869.901,1086.1,869.901z
         M1086.911,882.921h-1.663v-10.383h1.663V882.921z"/>
    <path fill="#262425" d="M1098.377,882.921h-1.663v-5.922c0-2.203-0.803-3.306-2.413-3.306c-0.831,0-1.519,0.315-2.063,0.938
        c-0.544,0.626-0.815,1.415-0.815,2.367v5.922h-1.663v-10.383h1.663v1.724h0.041c0.783-1.312,1.918-1.967,3.406-1.967
        c1.136,0,2.005,0.365,2.606,1.101c0.601,0.732,0.902,1.793,0.902,3.177V882.921z"/>
    <path fill="#262425" d="M1109.853,882.087c0,3.814-1.825,5.723-5.474,5.723c-1.285,0-2.407-0.244-3.367-0.731v-1.665
        c1.169,0.652,2.285,0.975,3.346,0.975c2.556,0,3.833-1.357,3.833-4.076v-1.135h-0.041c-0.791,1.323-1.982,1.986-3.57,1.986
        c-1.291,0-2.329-0.461-3.118-1.384c-0.787-0.923-1.18-2.163-1.18-3.716c0-1.764,0.424-3.167,1.272-4.205
        c0.849-1.043,2.008-1.563,3.482-1.563c1.4,0,2.437,0.561,3.113,1.683h0.041v-1.439h1.662V882.087z M1108.191,878.228v-1.532
        c0-0.825-0.279-1.532-0.837-2.119c-0.556-0.588-1.252-0.883-2.083-0.883c-1.027,0-1.832,0.371-2.413,1.119
        c-0.581,0.747-0.872,1.795-0.872,3.141c0,1.155,0.279,2.077,0.837,2.772c0.557,0.692,1.296,1.037,2.214,1.037
        c0.933,0,1.691-0.331,2.277-0.993C1107.898,880.11,1108.191,879.262,1108.191,878.228z"/>
    <g>
        <path fill="#F26522" d="M1009.665,895.026c0,2.407-1.018,3.578-3.11,3.578c-2.175,0-3.233-1.214-3.233-3.712v-8.072h-2.133v8.17
            c0,3.65,1.75,5.501,5.197,5.501c3.591,0,5.413-1.92,5.413-5.708v-7.963h-2.134V895.026z"/>
        <path fill="#95A43A" d="M1016.529,887.687l-2.096,0.648v2.181h-1.659v1.789h1.659v5.201c0,2.452,1.53,2.965,2.812,2.965
            c0.641,0,1.171-0.105,1.575-0.319l0.141-0.073v-1.946l-0.44,0.318c-0.236,0.173-0.512,0.255-0.843,0.255
            c-0.419,0-0.714-0.101-0.878-0.297c-0.178-0.215-0.271-0.617-0.271-1.164v-4.939h2.433v-1.789h-2.433V887.687z"/>
        <path fill="#00A0AF" d="M1024.53,890.3c-1.577,0-2.853,0.467-3.789,1.386c-0.934,0.919-1.407,2.204-1.407,3.818
            c0,1.489,0.455,2.704,1.353,3.612c0.902,0.911,2.12,1.375,3.617,1.375c1.537,0,2.783-0.477,3.703-1.412
            c0.916-0.934,1.38-2.184,1.38-3.72c0-1.564-0.431-2.808-1.283-3.703C1027.245,890.757,1026.044,890.3,1024.53,890.3z
             M1024.417,898.723c-0.907,0-1.607-0.279-2.143-0.857c-0.534-0.581-0.807-1.394-0.807-2.415c0-1.067,0.269-1.908,0.802-2.503
            c0.527-0.592,1.23-0.877,2.148-0.877c0.922,0,1.606,0.274,2.093,0.84c0.494,0.577,0.745,1.418,0.745,2.505
            c0,1.073-0.251,1.904-0.744,2.474C1026.023,898.45,1025.339,898.723,1024.417,898.723z"/>
        <path fill="#F26522" d="M1036.021,890.3c-1.281,0-2.322,0.447-3.101,1.33v-1.114h-2.094v14.008h2.094v-5.114
            c0.688,0.721,1.578,1.082,2.659,1.082c1.444,0,2.6-0.501,3.438-1.488c0.822-0.973,1.237-2.278,1.237-3.876
            c0-1.446-0.371-2.618-1.106-3.487C1038.399,890.75,1037.347,890.3,1036.021,890.3z M1038.124,895.099
            c0,1.156-0.25,2.066-0.743,2.701c-0.481,0.62-1.128,0.923-1.98,0.923c-0.727,0-1.306-0.235-1.775-0.715
            c-0.473-0.486-0.705-1.064-0.705-1.764v-1.288c0-0.847,0.247-1.526,0.753-2.077c0.498-0.546,1.139-0.809,1.962-0.809
            c0.768,0,1.359,0.253,1.805,0.776C1037.894,893.38,1038.124,894.138,1038.124,895.099z"/>
        <rect x="1041.694" y="890.516" fill="#00A0AF" width="2.097" height="9.76"/>
        <path fill="#00A0AF" d="M1042.761,886.25c-0.35,0-0.652,0.119-0.899,0.353c-0.248,0.232-0.375,0.528-0.375,0.875
            c0,0.352,0.127,0.644,0.376,0.874c0.247,0.229,0.549,0.346,0.897,0.346c0.357,0,0.664-0.121,0.916-0.356
            c0.25-0.234,0.377-0.526,0.377-0.863c0-0.347-0.129-0.645-0.381-0.877C1043.424,886.369,1043.116,886.25,1042.761,886.25z"/>
        <path fill="#95A43A" d="M1049.67,890.3c-1.286,0-2.438,0.307-3.427,0.906l-0.129,0.077v2.226l0.451-0.367
            c0.882-0.71,1.883-1.071,2.973-1.071c0.705,0,1.632,0.197,1.71,1.864l-2.654,0.354c-2.271,0.3-3.422,1.4-3.422,3.27
            c0,0.88,0.303,1.595,0.902,2.134c0.593,0.527,1.415,0.799,2.444,0.799c1.127,0,2.043-0.387,2.734-1.15v0.935h2.095v-6.268
            C1053.348,891.583,1052.077,890.3,1049.67,890.3z M1051.253,895.643v0.62c0,0.724-0.222,1.305-0.676,1.771
            c-0.449,0.465-1.004,0.688-1.699,0.688c-0.494,0-0.874-0.118-1.16-0.361c-0.279-0.238-0.413-0.533-0.413-0.909
            c0-0.532,0.141-0.887,0.43-1.078c0.327-0.218,0.833-0.376,1.502-0.465L1051.253,895.643z"/>
    </g>
    <g>
        <path fill="#262425" d="M958.178,801.517c0-6.9,3.545-13.018,9.005-16.807c1.097-10.533,10.482-18.761,21.897-18.761
            c0.353,0,0.701,0.009,1.05,0.024c2.766-2.813,6.344-4.9,10.381-5.927c1.871-0.477,3.8-0.714,5.728-0.714
            c2.893,0,5.654,0.529,8.183,1.489c0.136-0.052,0.271-0.102,0.407-0.15c0.005-0.003,0.011-0.005,0.018-0.006
            c0.137-0.049,0.275-0.097,0.414-0.145l0,0c2.297-0.769,4.77-1.188,7.345-1.188c1.812,0,3.572,0.208,5.257,0.6
            c1.977-0.867,4.179-1.352,6.499-1.352c5.963,0,11.136,3.19,13.726,7.862c5.162,0.386,9.513,3.487,11.466,7.794
            c5.33,1.197,9.524,5.863,9.524,11.413c0,0.534-0.038,1.057-0.11,1.569c1.992,1.933,3.431,4.417,4.05,7.131
            c0.619,2.703,0.409,5.574-0.595,8.16c-0.554,1.426-1.342,2.759-2.314,3.941c-0.698,0.87-1.679,1.479-2.376,2.314
            c-1.635,1.278-3.551,2.244-5.646,2.803c-0.03,7.037-6.064,12.731-13.504,12.731c-0.073,0-0.145,0-0.217-0.003
            c-1.893,1.855-4.549,3.01-7.488,3.01c-3.406,0-6.429-1.55-8.328-3.942c-3.428,7.285-11.143,12.363-20.112,12.363
            c-3.682,0-7.152-0.854-10.2-2.367c-2.941,4.491-8.195,7.48-14.188,7.48c-5.458,0-10.306-2.479-13.348-6.318
            c-8.613-0.08-15.57-6.714-15.57-14.885c0-2.728,0.775-5.286,2.13-7.484C959.302,809.041,958.178,805.403,958.178,801.517z"/>
        <path fill="#00A0AF" d="M1020.521,824.75c-0.603-1.424-1.464-2.698-2.558-3.794c-1.097-1.096-2.373-1.956-3.794-2.559
            c-1.473-0.623-3.038-0.939-4.646-0.939h-30.704c-4.917,0-8.918-3.999-8.918-8.917v-3.728h-3.019v3.728
            c0,1.61,0.314,3.174,0.939,4.647c0.601,1.422,1.461,2.698,2.557,3.797c1.095,1.094,2.372,1.953,3.792,2.556
            c1.473,0.62,3.036,0.939,4.648,0.939h30.704c4.917,0,8.917,4,8.917,8.914v39.533h3.02v-39.533
            C1021.46,827.785,1021.144,826.222,1020.521,824.75z"/>
        <path fill="#95A43A" d="M1015.616,800.849c-0.602-1.419-1.462-2.697-2.557-3.79c-1.097-1.099-2.374-1.957-3.793-2.561
            c-1.475-0.622-3.037-0.938-4.648-0.938h-9.645v3.021h9.645c4.917,0,8.917,3.998,8.917,8.916v63.432h3.021v-63.432
            C1016.556,803.886,1016.238,802.321,1015.616,800.849z"/>
        <path fill="#F7941E" d="M1038.756,781.813v42.214c0,4.918-4,8.917-8.917,8.917h-9.23c-1.611,0-3.175,0.316-4.648,0.937
            c-1.42,0.603-2.697,1.464-3.793,2.56c-1.097,1.097-1.956,2.371-2.557,3.795c-0.624,1.471-0.938,3.034-0.938,4.647v24.046h3.019
            v-24.046c0-4.919,4-8.919,8.918-8.919h9.23c1.611,0,3.175-0.316,4.646-0.939c1.422-0.603,2.698-1.462,3.794-2.556
            c1.096-1.098,1.956-2.373,2.557-3.793c0.624-1.475,0.939-3.039,0.939-4.648v-42.214H1038.756z"/>
        <path fill="#FEBC11" d="M985.213,797.921v22.122c0,4.917,4.001,8.916,8.917,8.916h0.793c1.611,0,3.174,0.317,4.646,0.94
            c1.422,0.6,2.697,1.46,3.794,2.557c1.096,1.096,1.957,2.372,2.558,3.793c0.623,1.475,0.938,3.035,0.938,4.647v28.031h-3.019
            v-28.031c0-4.917-4.001-8.92-8.917-8.92l-0.793,0.004c-1.61,0-3.174-0.315-4.647-0.939c-1.42-0.602-2.697-1.463-3.792-2.557
            c-1.096-1.097-1.958-2.373-2.558-3.794c-0.623-1.475-0.939-3.037-0.939-4.647v-22.122H985.213z"/>
        <path fill="#F26522" d="M1055.444,798.887v9.286c0,4.918-4,8.918-8.917,8.918l-11.321-0.007c-1.612,0-3.175,0.316-4.647,0.939
            c-1.421,0.602-2.698,1.46-3.794,2.558c-1.095,1.096-1.956,2.371-2.556,3.794c-0.625,1.474-0.939,3.037-0.939,4.646v39.907h3.02
            v-39.907c0-4.915,3.999-8.915,8.915-8.915l1.473,0.006h9.851c1.609,0,3.174-0.317,4.646-0.94c1.422-0.602,2.698-1.462,3.794-2.556
            c1.096-1.1,1.957-2.374,2.557-3.796c0.623-1.474,0.938-3.036,0.938-4.646v-9.286H1055.444z"/>
        <path fill="#F7941E" d="M1020.681,781.275c3.161-1.96,7.61-3.181,12.539-3.181c9.596,0,17.373,4.629,17.373,10.337
            c0,5.454-7.101,9.922-16.104,10.309c-0.42,0.02-0.842,0.027-1.269,0.027c-1.084,0-2.144-0.059-3.172-0.171
            c-0.1,0.479-0.151,0.973-0.151,1.479c0,1.531,0.479,2.95,1.294,4.116c-2.824-0.816-4.929-3.321-5.161-6.35
            c-6.009-1.624-10.188-5.229-10.188-9.411C1015.843,785.654,1017.685,783.133,1020.681,781.275z"/>
        <g>
            <path fill="#00ADBA" d="M1014.985,775.923c-0.033-1.445-0.357-2.906-1.003-4.3c-0.04-0.086-0.08-0.169-0.12-0.252
                c0.492-0.344,0.977-0.701,1.456-1.069c-0.423-0.806-0.936-1.564-1.524-2.258c-0.526,0.298-1.042,0.608-1.549,0.932
                c-1.068-1.195-2.375-2.122-3.817-2.743c0.158-0.574,0.3-1.156,0.431-1.743c-0.849-0.338-1.734-0.591-2.64-0.744
                c-0.212,0.565-0.411,1.129-0.591,1.7c-1.547-0.222-3.156-0.12-4.724,0.346c-0.25-0.539-0.519-1.071-0.799-1.597
                c-0.875,0.281-1.717,0.658-2.514,1.117c0.199,0.563,0.413,1.118,0.641,1.67c-1.398,0.854-2.552,1.98-3.421,3.273
                c-0.543-0.247-1.094-0.482-1.651-0.705c-0.493,0.774-0.903,1.599-1.217,2.458c0.52,0.298,1.045,0.583,1.577,0.857
                c-0.505,1.488-0.693,3.082-0.522,4.671c-0.58,0.158-1.157,0.332-1.732,0.517c0.121,0.903,0.342,1.792,0.654,2.647
                c0.594-0.105,1.187-0.225,1.772-0.357c0.078,0.195,0.162,0.391,0.252,0.582c0.593,1.279,1.406,2.389,2.374,3.302
                c-0.345,0.492-0.681,0.991-1.004,1.498c0.679,0.61,1.425,1.147,2.219,1.597c0.392-0.459,0.771-0.928,1.137-1.403
                c1.396,0.744,2.948,1.183,4.544,1.279c0.048,0.592,0.114,1.185,0.193,1.777c0.918,0.031,1.842-0.034,2.748-0.2
                c0.004-0.599-0.004-1.198-0.03-1.791c0.823-0.172,1.64-0.438,2.435-0.808c0.682-0.317,1.316-0.693,1.9-1.12
                c0.424,0.418,0.856,0.828,1.302,1.228c0.73-0.56,1.398-1.204,1.989-1.906c-0.382-0.457-0.777-0.905-1.182-1.343
                c1-1.252,1.712-2.698,2.096-4.23c0.597,0.047,1.198,0.084,1.8,0.104c0.2-0.89,0.3-1.803,0.304-2.716
                C1016.175,776.088,1015.581,775.997,1014.985,775.923z M1007.3,783.43c-4.036,1.874-8.823,0.12-10.697-3.914
                c-1.872-4.033-0.12-8.821,3.915-10.695c4.034-1.873,8.822-0.121,10.696,3.913C1013.088,776.77,1011.335,781.555,1007.3,783.43z"/>
                <ellipse transform="matrix(-0.4212 -0.907 0.907 -0.4212 722.785 2013.7996)" fill="#00ADBA" cx="1003.997" cy="776.259" rx="3.481" ry="3.523"/>
        </g>
        <polygon fill="#FAA21B" points="974.813,824.9 973.701,823.789 972.207,827.469 970.713,831.146 974.393,829.654 978.07,828.16
            976.958,827.046 979.474,824.53 977.329,822.387 	"/>
        <path fill="#008FAF" d="M990.979,805.215c0,0-2.888,4.22-2.888,5.815c0,1.597,1.293,2.89,2.888,2.89
            c1.597,0,2.891-1.293,2.891-2.89C993.87,809.435,990.979,805.215,990.979,805.215z"/>
        <path fill="#FEBC11" d="M1064.613,799.622c0.019-0.546-0.021-1.09-0.118-1.626c-0.357-0.001-0.714,0.006-1.071,0.023
            c-0.195-0.952-0.596-1.821-1.147-2.563c0.25-0.254,0.496-0.514,0.736-0.779c-0.337-0.43-0.718-0.823-1.137-1.171
            c-0.274,0.229-0.543,0.464-0.805,0.706c-0.735-0.58-1.596-1.004-2.528-1.227c0.032-0.355,0.051-0.713,0.063-1.069
            c-0.533-0.113-1.078-0.17-1.623-0.167c-0.064,0.352-0.117,0.706-0.162,1.06c-0.126,0.003-0.253,0.013-0.379,0.022
            c-0.841,0.074-1.633,0.307-2.346,0.662c-0.205-0.291-0.419-0.578-0.638-0.859c-0.482,0.254-0.937,0.565-1.351,0.917
            c0.176,0.311,0.363,0.615,0.556,0.914c-0.703,0.63-1.267,1.406-1.646,2.277c-0.344-0.093-0.692-0.176-1.042-0.251
            c-0.206,0.507-0.356,1.034-0.449,1.573c0.336,0.122,0.675,0.236,1.016,0.343c-0.071,0.491-0.088,1.001-0.042,1.518
            c0.04,0.442,0.123,0.873,0.243,1.284c-0.322,0.151-0.641,0.31-0.957,0.48c0.166,0.518,0.392,1.019,0.667,1.488
            c0.337-0.121,0.669-0.251,0.998-0.388c0.5,0.809,1.171,1.494,1.957,2.015c-0.151,0.324-0.292,0.652-0.427,0.982
            c0.461,0.29,0.955,0.528,1.468,0.715c0.181-0.309,0.352-0.623,0.516-0.939c0.822,0.271,1.711,0.383,2.63,0.304
            c0.055-0.006,0.109-0.013,0.164-0.018c0.094,0.343,0.196,0.686,0.306,1.026c0.54-0.075,1.072-0.21,1.585-0.398
            c-0.062-0.351-0.132-0.702-0.21-1.049c0.892-0.356,1.68-0.898,2.324-1.578c0.293,0.205,0.591,0.399,0.895,0.59
            c0.366-0.405,0.688-0.847,0.958-1.321c-0.275-0.229-0.553-0.455-0.837-0.67c0.44-0.819,0.71-1.735,0.77-2.703
            C1063.903,799.714,1064.259,799.673,1064.613,799.622z"/>
        <path fill="#F7941E" d="M999.394,811.384c2.687,2.651,7.014,2.621,9.665-0.068c2.65-2.685,2.621-7.013-0.066-9.663
            c-2.688-2.651-7.015-2.621-9.667,0.066C996.675,804.405,996.705,808.731,999.394,811.384z M1000.316,810.67l0.904-0.81l1.761-1.575
            c0.338,0.231,0.726,0.354,1.12,0.372l0.463,2.318l0.238,1.188C1003.214,812.336,1001.568,811.838,1000.316,810.67z
             M1008.236,810.506c-0.7,0.711-1.541,1.197-2.433,1.46l-0.239-1.189l-0.463-2.317c0.223-0.106,0.432-0.251,0.616-0.438
            c0.136-0.137,0.249-0.287,0.34-0.448l2.275,0.644l1.167,0.327C1009.229,809.258,1008.808,809.928,1008.236,810.506z
             M1009.776,807.564l-1.168-0.329l-2.274-0.643c0.011-0.371-0.071-0.747-0.25-1.085l1.761-1.574l0.903-0.811
            C1009.716,804.421,1010.06,806.039,1009.776,807.564z M1008.069,802.364l-0.905,0.81l-1.76,1.575
            c-0.338-0.231-0.727-0.354-1.12-0.373l-0.463-2.316l-0.237-1.188C1005.169,800.698,1006.817,801.196,1008.069,802.364z
             M1000.147,802.529c0.701-0.712,1.542-1.196,2.434-1.46l0.239,1.19l0.463,2.316c-0.225,0.107-0.434,0.251-0.618,0.437
            c-0.134,0.139-0.248,0.29-0.338,0.448l-2.275-0.64l-1.168-0.331C999.156,803.777,999.578,803.107,1000.147,802.529z
             M999.776,805.802l2.275,0.64c-0.012,0.374,0.071,0.748,0.25,1.085l-1.762,1.576l-0.903,0.81c-0.969-1.298-1.312-2.916-1.028-4.44
            L999.776,805.802z"/>
        <g>
            <polygon fill="#F6891F" points="1031.815,809.336 1026.787,810.374 1027.809,811.959 1023.89,814.482 1025.858,817.536
                1029.775,815.014 1030.795,816.599 1033.821,812.449 1036.846,808.299 		"/>
                <rect x="1021.338" y="815.926" transform="matrix(-0.5419 -0.8404 0.8404 -0.5419 890.778 2119.8359)" fill="#F6891F" width="3.481" height="2.481"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="987.423,798.271 990.196,796.184 992.283,798.956 991.458,799.577 990.431,798.216
                990.015,801.17 988.99,801.024 989.408,798.07 988.045,799.096 		"/>
            <polygon fill="#AAD9B5" points="991.172,794.962 990.511,794.603 991.548,792.69 990.356,793.045 990.142,792.321
                992.566,791.602 993.286,794.029 992.564,794.241 992.21,793.049 		"/>
        </g>
        <polygon fill="#F26522" points="1054.211,774.397 1051.273,774.397 1051.273,771.46 1049.228,771.46 1049.228,774.397
            1046.29,774.397 1046.29,776.442 1049.228,776.442 1049.228,779.381 1051.273,779.381 1051.273,776.442 1054.211,776.442 	"/>
        <polygon fill="#F5EB00" points="972.932,797.2 969.802,797.2 969.802,794.072 967.625,794.072 967.625,797.2 964.495,797.2
            964.495,799.38 967.625,799.38 967.625,802.508 969.802,802.508 969.802,799.38 972.932,799.38 	"/>
        <polygon fill="#8DC63F" points="1039.434,768.292 1041.083,766.244 1034.704,764.423 1028.323,762.599 1031.464,768.442
            1034.603,774.289 1036.254,772.239 1040.884,775.97 1044.064,772.023 	"/>
        <polygon fill="#4EA0B4" points="1020.474,769.193 1022.249,770.39 1018.665,775.702 1019.264,776.106 1022.849,770.794
            1024.624,771.992 1024.8,769.495 1024.974,766.998 	"/>
        <path fill="#00A0AF" d="M1009.171,824.163c-1.353-1.351-3.546-1.351-4.898,0c-1.353,1.354-1.353,3.548,0,4.9s3.545,1.353,4.898,0
            C1010.524,827.711,1010.524,825.518,1009.171,824.163z M1007.635,827.526c-0.504,0.505-1.322,0.505-1.825,0
            c-0.504-0.507-0.504-1.321,0-1.826c0.502-0.504,1.321-0.504,1.825,0C1008.139,826.205,1008.139,827.02,1007.635,827.526z"/>
        <polygon fill="#79AD36" points="983.412,837.342 984.223,834.892 991.558,837.319 991.833,836.491 984.498,834.063 985.309,831.61
            982.354,832.22 979.399,832.833 	"/>
        <polygon fill="#17AEB2" points="1021.104,808.41 1021.92,808.367 1021.773,805.611 1024.628,805.543 1021.122,799.794
            1017.895,805.707 1020.958,805.63 	"/>
        <g>
            <path fill="#BFE2CE" d="M986.201,784.45l-4.008-0.665l0.667-4.006c0.067-0.401-0.205-0.782-0.606-0.849
                c-0.404-0.068-0.783,0.204-0.851,0.607l-0.666,4.005l-4.006-0.668c-0.401-0.065-0.783,0.206-0.849,0.606
                c-0.067,0.404,0.205,0.784,0.605,0.851l4.008,0.668l-0.668,4.005c-0.066,0.401,0.205,0.782,0.609,0.85
                c0.401,0.066,0.78-0.205,0.848-0.608l0.667-4.006l4.006,0.667c0.403,0.067,0.783-0.205,0.85-0.606
                C986.874,784.899,986.603,784.519,986.201,784.45z"/>
            <path fill="#95A43A" d="M990.005,784.385c0.499-0.08,0.998-0.173,1.496-0.277c-0.023-0.803-0.141-1.6-0.351-2.372
                c-0.506,0.037-1.013,0.09-1.514,0.156c-0.277-0.923-0.708-1.801-1.289-2.598c0.358-0.356,0.708-0.726,1.048-1.104
                c-0.49-0.636-1.058-1.213-1.681-1.715c-0.388,0.331-0.766,0.672-1.132,1.021c-0.797-0.606-1.666-1.052-2.568-1.346
                c0.079-0.5,0.145-1.004,0.198-1.508c-0.77-0.228-1.566-0.361-2.368-0.399c-0.119,0.494-0.224,0.99-0.316,1.49
                c-0.971-0.021-1.941,0.125-2.867,0.423c-0.229-0.451-0.471-0.896-0.725-1.339c-0.756,0.269-1.479,0.628-2.152,1.066
                c0.194,0.47,0.4,0.937,0.619,1.392c-0.714,0.498-1.365,1.11-1.924,1.837c-0.049,0.064-0.099,0.129-0.144,0.195
                c-0.45-0.232-0.91-0.451-1.374-0.657c-0.452,0.66-0.83,1.375-1.114,2.127c0.433,0.267,0.873,0.522,1.318,0.763
                c-0.324,0.929-0.481,1.896-0.48,2.857c-0.5,0.079-0.999,0.174-1.495,0.279c0.021,0.8,0.139,1.599,0.349,2.373
                c0.507-0.04,1.012-0.092,1.515-0.158c0.276,0.921,0.709,1.802,1.289,2.597c-0.359,0.357-0.708,0.727-1.047,1.104
                c0.49,0.635,1.058,1.211,1.68,1.714c0.388-0.332,0.765-0.671,1.132-1.019c0.797,0.604,1.666,1.053,2.568,1.344
                c-0.079,0.5-0.146,1.002-0.198,1.509c0.771,0.227,1.567,0.359,2.367,0.399c0.12-0.494,0.225-0.992,0.316-1.489
                c0.972,0.019,1.942-0.124,2.867-0.426c0.229,0.451,0.471,0.897,0.727,1.34c0.754-0.27,1.479-0.626,2.151-1.067
                c-0.192-0.471-0.401-0.935-0.619-1.393c0.715-0.496,1.365-1.109,1.923-1.835c0.051-0.065,0.098-0.13,0.147-0.195
                c0.451,0.23,0.908,0.451,1.374,0.658c0.45-0.662,0.828-1.377,1.112-2.127c-0.432-0.268-0.872-0.521-1.318-0.766
                C989.847,786.313,990.005,785.349,990.005,784.385z"/>
            <g>
                <path fill="#A9BF38" d="M980.462,789.692c2.927,0.488,5.696-1.491,6.182-4.418c0.487-2.928-1.491-5.696-4.419-6.183
                    c-2.927-0.486-5.695,1.491-6.182,4.418C975.556,786.438,977.535,789.205,980.462,789.692z M981.993,780.49
                    c2.156,0.357,3.612,2.397,3.252,4.552c-0.358,2.155-2.396,3.611-4.552,3.253c-2.156-0.358-3.611-2.397-3.253-4.552
                    C977.8,781.587,979.838,780.131,981.993,780.49z"/>
                <circle fill="#A9BF38" cx="981.344" cy="784.392" r="2.003"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M1060.243,778.646c-3.032-0.014-5.504,2.433-5.516,5.464c-0.014,3.034,2.433,5.504,5.465,5.518
                s5.503-2.431,5.516-5.465C1065.723,781.131,1063.275,778.663,1060.243,778.646z M1060.199,788.18
                c-2.233-0.01-4.036-1.828-4.025-4.061c0.01-2.232,1.83-4.036,4.063-4.025c2.232,0.012,4.034,1.83,4.023,4.063
                C1064.251,786.39,1062.432,788.19,1060.199,788.18z"/>
            <circle fill="#CFB52B" cx="1060.217" cy="784.138" r="2.046"/>
        </g>
        <circle fill="#8DC63F" cx="976.102" cy="809.902" r="2.805"/>
        <g>
            <path fill="#FEBC11" d="M963.74,821.077c-0.45-0.315-0.715-0.712-0.791-1.188c-0.077-0.479,0.053-0.957,0.39-1.44
                c0.368-0.526,0.807-0.845,1.315-0.949c0.509-0.102,1.006,0.014,1.497,0.355c0.467,0.327,0.735,0.722,0.804,1.185
                c0.069,0.463-0.073,0.948-0.427,1.455c-0.362,0.521-0.798,0.834-1.302,0.936C964.72,821.534,964.225,821.415,963.74,821.077z
                 M970.603,821.09l-8.542,3.456l-1.077-0.749l8.533-3.464L970.603,821.09z M965.583,818.611c-0.432-0.298-0.853-0.148-1.271,0.447
                c-0.394,0.566-0.386,0.992,0.024,1.276c0.417,0.293,0.832,0.146,1.242-0.445C965.977,819.316,965.979,818.89,965.583,818.611z
                 M965.444,827.036c-0.451-0.314-0.715-0.708-0.79-1.186c-0.078-0.478,0.053-0.959,0.391-1.442
                c0.366-0.526,0.805-0.841,1.314-0.947c0.509-0.105,1.006,0.014,1.495,0.356c0.472,0.326,0.742,0.722,0.812,1.18
                c0.067,0.462-0.074,0.943-0.427,1.451c-0.365,0.52-0.8,0.832-1.306,0.939C966.426,827.492,965.93,827.377,965.444,827.036z
                 M967.273,824.565c-0.427-0.297-0.848-0.148-1.266,0.449c-0.394,0.565-0.384,0.992,0.031,1.282
                c0.418,0.292,0.833,0.143,1.244-0.446c0.189-0.275,0.283-0.524,0.275-0.753C967.551,824.87,967.456,824.692,967.273,824.565z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M1049.59,811.703l0.601,0.914l-0.615,0.402l-0.583-0.887c-0.573,0.374-1.188,0.591-1.847,0.659
                l-0.768-1.166c0.249,0.024,0.572-0.014,0.97-0.113c0.396-0.099,0.727-0.224,0.99-0.375l-1.009-1.531
                c-0.816,0.188-1.439,0.219-1.867,0.092c-0.426-0.127-0.774-0.396-1.045-0.808c-0.289-0.439-0.38-0.918-0.273-1.431
                c0.107-0.516,0.401-0.968,0.881-1.357l-0.516-0.783l0.615-0.403l0.504,0.767c0.61-0.363,1.116-0.558,1.516-0.586l0.747,1.138
                c-0.55,0.017-1.088,0.163-1.613,0.434l1.049,1.595c0.761-0.183,1.368-0.219,1.822-0.109c0.453,0.109,0.813,0.366,1.08,0.771
                c0.307,0.465,0.405,0.939,0.294,1.419C1050.415,810.824,1050.103,811.278,1049.59,811.703z M1046.552,808.424l-0.877-1.335
                c-0.345,0.326-0.405,0.659-0.184,0.995C1045.681,808.373,1046.035,808.486,1046.552,808.424z M1048.104,809.441l0.839,1.275
                c0.361-0.324,0.428-0.656,0.205-0.997C1048.969,809.445,1048.619,809.353,1048.104,809.441z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M1038.771,792.857l-1.348-0.563l-1.003,2.39l-2.258-0.947l1.001-2.39l-4.898-2.054l0.659-1.575
                l7.769-5.299l2.439,1.021l-2.97,7.089l1.35,0.564L1038.771,792.857z M1037.868,784.986l-0.047-0.021
                c-0.152,0.157-0.416,0.391-0.792,0.7l-3.97,2.725l2.846,1.192l1.482-3.536C1037.519,785.734,1037.678,785.38,1037.868,784.986z"/>
        </g>
    </g>
    <g>
        <path fill="#262425" d="M1178.357,1059.967l-0.263-0.108c-0.271-0.109-0.627-0.165-1.09-0.165c-0.664,0-1.269,0.226-1.801,0.673
            c-0.127,0.104-0.245,0.222-0.353,0.349v-0.853h-2.408v10.567h2.408v-5.379c0-0.986,0.197-1.764,0.587-2.313
            c0.366-0.511,0.797-0.762,1.319-0.762c0.415,0,0.723,0.078,0.917,0.227l0.683,0.527V1059.967z"/>
        <path fill="#262425" d="M1184.273,1059.636c-1.63,0-2.949,0.506-3.919,1.504c-0.967,0.995-1.458,2.381-1.458,4.119
            c0,1.604,0.472,2.918,1.402,3.902c0.936,0.991,2.196,1.494,3.747,1.494c1.589,0,2.879-0.517,3.834-1.536
            c0.948-1.008,1.427-2.357,1.427-4.012c0-1.683-0.446-3.025-1.329-3.994C1187.086,1060.132,1185.839,1059.636,1184.273,1059.636z
             M1186.15,1067.673c-0.46,0.552-1.109,0.822-1.99,0.822c-0.867,0-1.535-0.279-2.044-0.854c-0.514-0.579-0.775-1.402-0.775-2.44
            c0-1.083,0.259-1.938,0.771-2.533c0.5-0.589,1.171-0.872,2.048-0.872c0.88,0,1.529,0.272,1.989,0.831
            c0.475,0.576,0.716,1.432,0.716,2.536C1186.865,1066.259,1186.625,1067.101,1186.15,1067.673z"/>
        <polygon fill="#262425" points="1201.822,1059.863 1199.983,1066.689 1198.11,1059.863 1196.047,1059.863 1193.942,1066.713
            1192.058,1059.863 1189.53,1059.863 1192.727,1070.43 1194.912,1070.43 1196.954,1063.875 1198.839,1070.43 1201.088,1070.43
            1204.256,1059.863 	"/>
        <path fill="#262425" d="M1211.876,1060.492c-0.649-0.566-1.495-0.855-2.523-0.855c-1.473,0-2.672,0.539-3.562,1.604
            c-0.871,1.043-1.313,2.435-1.313,4.134c0,1.595,0.402,2.882,1.195,3.827c0.811,0.962,1.904,1.455,3.252,1.455
            c1.206,0,2.193-0.372,2.95-1.105v0.88h2.406v-15.232h-2.406V1060.492z M1211.168,1067.703c-0.459,0.533-1.044,0.793-1.787,0.793
            c-0.75,0-1.328-0.268-1.77-0.813c-0.459-0.569-0.689-1.371-0.689-2.385c0-1.123,0.246-2.005,0.727-2.622
            c0.461-0.592,1.086-0.88,1.902-0.88c0.681,0,1.226,0.222,1.666,0.685c0.443,0.465,0.659,1.021,0.659,1.698v1.432
            C1211.876,1066.468,1211.645,1067.153,1211.168,1067.703z"/>
        <path fill="#262425" d="M1223.133,1066.213c-0.154-0.34-0.374-0.638-0.656-0.896c-0.27-0.243-0.593-0.461-0.967-0.65
            c-0.352-0.181-0.754-0.355-1.193-0.524c-0.318-0.125-0.604-0.242-0.852-0.346c-0.221-0.094-0.408-0.198-0.557-0.308
            c-0.127-0.097-0.223-0.204-0.285-0.319c-0.057-0.105-0.086-0.253-0.086-0.433c0-0.133,0.029-0.247,0.086-0.351
            c0.061-0.112,0.147-0.206,0.264-0.291c0.127-0.09,0.287-0.164,0.47-0.217c0.196-0.054,0.423-0.083,0.673-0.083
            c0.84,0,1.592,0.212,2.232,0.634l0.658,0.431v-2.64l-0.254-0.111c-0.716-0.313-1.533-0.474-2.43-0.474
            c-0.508,0-1.01,0.067-1.49,0.196c-0.49,0.133-0.934,0.33-1.318,0.595c-0.398,0.271-0.723,0.615-0.961,1.017
            c-0.246,0.415-0.373,0.896-0.373,1.425c0,0.427,0.066,0.809,0.195,1.135c0.131,0.331,0.329,0.632,0.588,0.889
            c0.248,0.25,0.555,0.47,0.916,0.662c0.337,0.182,0.732,0.361,1.18,0.538c0.309,0.121,0.594,0.232,0.86,0.343
            c0.239,0.101,0.448,0.211,0.62,0.331c0.152,0.105,0.274,0.229,0.361,0.361c0.071,0.113,0.107,0.249,0.107,0.427
            c0,0.282,0,0.941-1.623,0.941c-0.934,0-1.768-0.282-2.547-0.855l-0.678-0.502v2.771l0.229,0.117c0.79,0.416,1.73,0.629,2.795,0.629
            c0.537,0,1.061-0.063,1.555-0.185c0.506-0.124,0.963-0.319,1.355-0.579c0.406-0.269,0.736-0.61,0.98-1.02
            c0.249-0.418,0.379-0.912,0.379-1.469C1223.367,1066.953,1223.286,1066.551,1223.133,1066.213z"/>
        <path fill="#262425" stroke="#000000" d="M1229.592,1071.037c-1.537,0-2.76-0.487-3.678-1.457
            c-0.916-0.973-1.375-2.257-1.375-3.859c0-1.743,0.479-3.105,1.432-4.085c0.953-0.98,2.24-1.47,3.861-1.47
            c1.547,0,2.756,0.477,3.627,1.429c0.867,0.953,1.301,2.275,1.301,3.965c0,1.656-0.467,2.982-1.404,3.98
            C1232.422,1070.535,1231.164,1071.037,1229.592,1071.037z M1229.711,1061.565c-1.068,0-1.914,0.362-2.535,1.09
            c-0.623,0.727-0.934,1.729-0.934,3.005c0,1.23,0.316,2.201,0.943,2.909c0.629,0.713,1.471,1.066,2.525,1.066
            c1.074,0,1.902-0.349,2.479-1.043c0.578-0.699,0.867-1.687,0.867-2.972c0-1.297-0.289-2.3-0.867-3.003
            C1231.613,1061.916,1230.785,1061.565,1229.711,1061.565z"/>
        <path fill="#262425" stroke="#000000" d="M1245.295,1070.792h-1.664v-1.643h-0.041c-0.689,1.256-1.758,1.888-3.205,1.888
            c-2.473,0-3.709-1.479-3.709-4.425v-6.203h1.65v5.94c0,2.189,0.84,3.286,2.516,3.286c0.811,0,1.479-0.298,2.002-0.896
            c0.523-0.601,0.787-1.382,0.787-2.349v-5.981h1.664V1070.792z"/>
        <path fill="#262425" stroke="#000000" d="M1253.545,1062.09c-0.291-0.222-0.709-0.335-1.258-0.335
            c-0.709,0-1.303,0.335-1.779,1.006c-0.477,0.669-0.717,1.581-0.717,2.737v5.293h-1.66v-10.383h1.66v2.139h0.043
            c0.236-0.729,0.598-1.299,1.084-1.708s1.031-0.613,1.631-0.613c0.436,0,0.766,0.048,0.996,0.141V1062.09z"/>
        <path fill="#262425" stroke="#000000" d="M1261.906,1070.313c-0.797,0.481-1.744,0.724-2.838,0.724
            c-1.48,0-2.674-0.482-3.584-1.447c-0.908-0.963-1.363-2.213-1.363-3.748c0-1.709,0.488-3.082,1.469-4.12s2.289-1.556,3.924-1.556
            c0.912,0,1.719,0.17,2.416,0.506v1.705c-0.771-0.541-1.598-0.812-2.475-0.812c-1.063,0-1.932,0.378-2.611,1.141
            c-0.682,0.761-1.02,1.758-1.02,2.997c0,1.215,0.32,2.174,0.959,2.88c0.639,0.699,1.494,1.053,2.57,1.053
            c0.904,0,1.756-0.302,2.553-0.902V1070.313z"/>
        <path fill="#262425" stroke="#000000" d="M1264.756,1057.772c-0.299,0-0.553-0.101-0.762-0.305
            c-0.207-0.203-0.313-0.461-0.313-0.77c0-0.312,0.105-0.571,0.313-0.777c0.209-0.206,0.463-0.308,0.762-0.308
            c0.305,0,0.563,0.102,0.773,0.308c0.215,0.206,0.32,0.466,0.32,0.777c0,0.298-0.105,0.549-0.32,0.76
            C1265.318,1057.669,1265.061,1057.772,1264.756,1057.772z M1265.566,1070.792h-1.664v-10.383h1.664V1070.792z"/>
        <path fill="#262425" stroke="#000000" d="M1277.031,1070.792h-1.662v-5.922c0-2.203-0.805-3.305-2.414-3.305
            c-0.83,0-1.518,0.312-2.064,0.937c-0.543,0.626-0.814,1.414-0.814,2.368v5.922h-1.662v-10.383h1.662v1.724h0.041
            c0.783-1.313,1.92-1.967,3.406-1.967c1.135,0,2.004,0.365,2.605,1.098c0.604,0.734,0.902,1.796,0.902,3.181V1070.792z"/>
        <path fill="#262425" stroke="#000000" d="M1288.508,1069.957c0,3.816-1.824,5.723-5.477,5.723c-1.283,0-2.404-0.245-3.363-0.73
            v-1.663c1.168,0.649,2.283,0.975,3.346,0.975c2.553,0,3.832-1.36,3.832-4.078v-1.136h-0.043c-0.789,1.323-1.979,1.99-3.566,1.99
            c-1.293,0-2.332-0.465-3.119-1.389c-0.787-0.92-1.182-2.159-1.182-3.715c0-1.765,0.424-3.166,1.273-4.208
            c0.848-1.04,2.01-1.56,3.482-1.56c1.398,0,2.436,0.56,3.111,1.682h0.043v-1.438h1.662V1069.957z M1286.846,1066.097v-1.531
            c0-0.825-0.279-1.532-0.838-2.119c-0.559-0.59-1.252-0.882-2.084-0.882c-1.027,0-1.83,0.373-2.412,1.119s-0.873,1.793-0.873,3.14
            c0,1.155,0.281,2.079,0.838,2.771c0.559,0.695,1.295,1.04,2.215,1.04c0.934,0,1.691-0.331,2.275-0.995
            C1286.551,1067.98,1286.846,1067.129,1286.846,1066.097z"/>
        <g>
            <path fill="#F26522" d="M1191.353,1081.077c0,2.407-1.019,3.579-3.111,3.579c-2.176,0-3.233-1.214-3.233-3.712v-8.073h-2.132v8.17
                c0,3.651,1.748,5.503,5.195,5.503c3.593,0,5.414-1.921,5.414-5.711v-7.962h-2.132V1081.077z"/>
            <path fill="#95A43A" d="M1198.216,1073.738l-2.097,0.648v2.181h-1.659v1.788h1.659v5.201c0,2.451,1.53,2.969,2.812,2.969
                c0.641,0,1.171-0.108,1.575-0.322l0.142-0.074v-1.945l-0.44,0.32c-0.236,0.17-0.512,0.253-0.844,0.253
                c-0.419,0-0.714-0.102-0.878-0.299c-0.178-0.214-0.271-0.615-0.271-1.162v-4.94h2.433v-1.788h-2.433V1073.738z"/>
            <path fill="#00A0AF" d="M1206.217,1076.351c-1.578,0-2.854,0.467-3.787,1.386c-0.936,0.919-1.408,2.203-1.408,3.819
                c0,1.486,0.455,2.703,1.352,3.61c0.901,0.913,2.119,1.377,3.617,1.377c1.537,0,2.783-0.476,3.702-1.413
                c0.917-0.934,1.382-2.185,1.382-3.72c0-1.564-0.432-2.808-1.283-3.702C1208.932,1076.808,1207.73,1076.351,1206.217,1076.351z
                 M1206.104,1084.773c-0.906,0-1.607-0.279-2.142-0.856c-0.536-0.58-0.809-1.392-0.809-2.415c0-1.067,0.271-1.908,0.803-2.505
                c0.528-0.59,1.23-0.877,2.147-0.877c0.922,0,1.607,0.277,2.094,0.845c0.494,0.572,0.744,1.414,0.744,2.502
                c0,1.073-0.25,1.904-0.744,2.473C1207.711,1084.502,1207.025,1084.773,1206.104,1084.773z"/>
            <path fill="#F26522" d="M1217.707,1076.351c-1.28,0-2.319,0.445-3.1,1.329v-1.113h-2.094v14.005h2.094v-5.108
                c0.688,0.716,1.578,1.08,2.658,1.08c1.443,0,2.6-0.502,3.438-1.49c0.823-0.974,1.24-2.277,1.24-3.877
                c0-1.444-0.373-2.616-1.108-3.487C1220.086,1076.802,1219.033,1076.351,1217.707,1076.351z M1219.811,1081.149
                c0,1.157-0.251,2.068-0.744,2.704c-0.479,0.617-1.128,0.92-1.98,0.92c-0.725,0-1.306-0.234-1.773-0.715
                c-0.475-0.485-0.705-1.061-0.705-1.763v-1.289c0-0.844,0.246-1.525,0.752-2.077c0.498-0.546,1.139-0.81,1.961-0.81
                c0.77,0,1.359,0.256,1.808,0.778C1219.58,1079.431,1219.811,1080.189,1219.811,1081.149z"/>
            <rect x="1223.381" y="1076.567" fill="#00A0AF" width="2.096" height="9.76"/>
            <path fill="#00A0AF" d="M1224.449,1072.302c-0.352,0-0.652,0.117-0.9,0.35c-0.249,0.233-0.375,0.529-0.375,0.877
                c0,0.351,0.126,0.644,0.377,0.875c0.246,0.23,0.549,0.345,0.898,0.345c0.355,0,0.662-0.119,0.912-0.354
                c0.252-0.237,0.379-0.527,0.379-0.865c0-0.348-0.129-0.644-0.381-0.877C1225.111,1072.419,1224.803,1072.302,1224.449,1072.302z"
                />
            <path fill="#95A43A" d="M1231.355,1076.351c-1.283,0-2.438,0.306-3.426,0.904l-0.129,0.08v2.224l0.451-0.364
                c0.883-0.713,1.881-1.075,2.971-1.075c0.707,0,1.637,0.2,1.713,1.865l-2.654,0.354c-2.271,0.301-3.422,1.401-3.422,3.271
                c0,0.878,0.303,1.593,0.902,2.132c0.592,0.528,1.414,0.801,2.445,0.801c1.127,0,2.041-0.388,2.732-1.148v0.932h2.096v-6.268
                C1235.035,1077.633,1233.764,1076.351,1231.355,1076.351z M1232.939,1081.693v0.62c0,0.725-0.221,1.306-0.676,1.774
                c-0.451,0.461-1.004,0.686-1.699,0.686c-0.496,0-0.873-0.118-1.16-0.36c-0.279-0.237-0.412-0.534-0.412-0.909
                c0-0.534,0.139-0.886,0.428-1.078c0.328-0.22,0.834-0.375,1.502-0.465L1232.939,1081.693z"/>
        </g>
        <path fill="#262425" d="M1141.38,977.258c0-6.9,3.544-13.018,9.004-16.807c1.098-10.53,10.483-18.76,21.898-18.76
            c0.353,0,0.702,0.008,1.05,0.023c2.767-2.813,6.343-4.9,10.383-5.927c1.869-0.475,3.797-0.713,5.727-0.713
            c2.892,0,5.653,0.528,8.183,1.489c0.134-0.053,0.27-0.103,0.406-0.15c0.005-0.003,0.011-0.004,0.017-0.006
            c0.137-0.05,0.275-0.097,0.416-0.144l0,0c2.296-0.771,4.768-1.189,7.342-1.189c1.813,0,3.574,0.207,5.258,0.598
            c1.977-0.866,4.18-1.351,6.5-1.351c5.963,0,11.135,3.188,13.727,7.862c5.158,0.386,9.514,3.487,11.465,7.794
            c5.33,1.197,9.523,5.863,9.523,11.415c0,0.532-0.039,1.055-0.109,1.569c1.992,1.934,3.432,4.417,4.051,7.129
            c0.619,2.704,0.406,5.573-0.596,8.159c-0.553,1.428-1.342,2.762-2.313,3.945c-0.701,0.866-1.682,1.478-2.377,2.313
            c-1.635,1.276-3.551,2.244-5.646,2.805c-0.029,7.032-6.064,12.73-13.504,12.73c-0.074,0-0.148-0.002-0.219-0.004
            c-1.893,1.856-4.547,3.011-7.488,3.011c-3.406,0-6.43-1.551-8.328-3.945c-3.426,7.287-11.143,12.366-20.111,12.366
            c-3.682,0-7.151-0.855-10.201-2.37c-2.941,4.493-8.194,7.48-14.188,7.48c-5.458,0-10.305-2.479-13.349-6.316
            c-8.612-0.082-15.568-6.714-15.568-14.885c0-2.729,0.777-5.287,2.13-7.485C1142.505,984.783,1141.38,981.143,1141.38,977.258z"/>
        <path fill="#00A0AF" d="M1203.725,1000.492c-0.604-1.423-1.463-2.7-2.561-3.794c-1.094-1.097-2.37-1.958-3.793-2.56
            c-1.473-0.621-3.037-0.936-4.646-0.936h-30.704c-4.917,0-8.917-4.004-8.917-8.92v-3.729h-3.021v3.729
            c0,1.612,0.317,3.176,0.94,4.646c0.6,1.423,1.462,2.7,2.558,3.796c1.094,1.096,2.371,1.955,3.792,2.557
            c1.473,0.625,3.036,0.939,4.647,0.939h30.704c4.917,0,8.917,4.002,8.917,8.916v49.908h3.02v-49.908
            C1204.662,1003.528,1204.346,1001.963,1203.725,1000.492z"/>
        <path fill="#95A43A" d="M1198.818,976.592c-0.601-1.422-1.462-2.698-2.558-3.795c-1.096-1.095-2.371-1.957-3.794-2.556
            c-1.472-0.625-3.035-0.941-4.647-0.941h-9.644v3.02h9.644c4.918,0,8.918,3.999,8.918,8.918v73.806h3.02v-73.806
            C1199.758,979.627,1199.441,978.064,1198.818,976.592z"/>
        <path fill="#F7941E" d="M1221.961,957.554v42.213c0,4.918-4.002,8.917-8.92,8.917h-9.229c-1.61,0-3.175,0.316-4.648,0.938
            c-1.42,0.603-2.697,1.464-3.792,2.558c-1.096,1.097-1.956,2.371-2.558,3.794c-0.622,1.472-0.938,3.036-0.938,4.647v34.423h3.019
            v-34.423c0-4.917,4-8.916,8.917-8.916h9.229c1.613,0,3.175-0.316,4.646-0.94c1.424-0.602,2.701-1.461,3.795-2.557
            c1.098-1.096,1.957-2.374,2.557-3.795c0.623-1.472,0.939-3.035,0.939-4.646v-42.213H1221.961z"/>
        <path fill="#FEBC11" d="M1168.416,973.662v22.123c0,4.915,4,8.915,8.917,8.915h0.794c1.61,0,3.174,0.317,4.646,0.938
            c1.422,0.604,2.698,1.462,3.794,2.558c1.095,1.097,1.956,2.373,2.557,3.795c0.623,1.472,0.939,3.035,0.939,4.646v38.407h-3.021
            v-38.407c0-4.918-3.999-8.917-8.916-8.917l-0.794,0.002c-1.61,0-3.173-0.316-4.647-0.94c-1.421-0.603-2.697-1.461-3.792-2.556
            c-1.095-1.097-1.956-2.373-2.558-3.797c-0.623-1.471-0.939-3.034-0.939-4.645v-22.123H1168.416z"/>
        <path fill="#F26522" d="M1238.645,974.628v9.285c0,4.918-4,8.917-8.914,8.917l-11.322-0.007c-1.611,0-3.174,0.318-4.648,0.94
            c-1.42,0.603-2.696,1.462-3.793,2.559c-1.096,1.095-1.955,2.371-2.557,3.791c-0.624,1.475-0.939,3.037-0.939,4.649v50.281h3.021
            v-50.281c0-4.919,3.999-8.92,8.915-8.92l1.473,0.009h9.852c1.611,0,3.172-0.315,4.646-0.94c1.42-0.602,2.697-1.462,3.793-2.558
            c1.096-1.098,1.955-2.372,2.559-3.794c0.621-1.472,0.938-3.037,0.938-4.646v-9.285H1238.645z"/>
        <path fill="#F7941E" d="M1203.883,957.015c3.162-1.959,7.611-3.179,12.539-3.179c9.594,0,17.375,4.628,17.375,10.336
            c0,5.457-7.104,9.923-16.107,10.309c-0.418,0.019-0.842,0.027-1.268,0.027c-1.084,0-2.142-0.059-3.17-0.172
            c-0.1,0.479-0.153,0.974-0.153,1.48c0,1.531,0.479,2.95,1.295,4.116c-2.823-0.818-4.929-3.319-5.16-6.348
            c-6.009-1.626-10.187-5.23-10.187-9.413C1199.047,961.395,1200.888,958.874,1203.883,957.015z"/>
        <g>
            <path fill="#00ADBA" d="M1198.187,951.664c-0.031-1.446-0.355-2.907-1.003-4.3c-0.039-0.084-0.079-0.168-0.12-0.251
                c0.494-0.344,0.98-0.7,1.456-1.069c-0.421-0.804-0.934-1.565-1.522-2.261c-0.525,0.3-1.042,0.612-1.55,0.935
                c-1.068-1.196-2.375-2.123-3.816-2.744c0.157-0.576,0.299-1.156,0.431-1.742c-0.849-0.34-1.734-0.591-2.64-0.743
                c-0.211,0.563-0.41,1.128-0.593,1.698c-1.544-0.223-3.155-0.119-4.723,0.346c-0.251-0.539-0.518-1.071-0.797-1.598
                c-0.877,0.284-1.718,0.659-2.516,1.117c0.201,0.564,0.414,1.121,0.642,1.67c-1.398,0.855-2.553,1.981-3.42,3.276
                c-0.545-0.249-1.095-0.484-1.652-0.707c-0.492,0.774-0.902,1.599-1.218,2.458c0.52,0.3,1.047,0.587,1.579,0.857
                c-0.507,1.487-0.693,3.083-0.522,4.672c-0.581,0.157-1.159,0.331-1.733,0.517c0.121,0.902,0.34,1.792,0.654,2.647
                c0.595-0.104,1.186-0.224,1.773-0.358c0.077,0.196,0.16,0.39,0.25,0.585c0.594,1.276,1.407,2.386,2.373,3.3
                c-0.344,0.49-0.678,0.989-1.003,1.499c0.68,0.608,1.427,1.144,2.221,1.595c0.392-0.46,0.772-0.928,1.138-1.402
                c1.394,0.743,2.946,1.181,4.543,1.277c0.048,0.592,0.113,1.188,0.192,1.778c0.918,0.033,1.841-0.034,2.748-0.199
                c0.005-0.599-0.003-1.196-0.029-1.792c0.823-0.173,1.637-0.438,2.433-0.808c0.682-0.318,1.316-0.694,1.9-1.12
                c0.422,0.419,0.856,0.829,1.302,1.228c0.73-0.56,1.396-1.201,1.99-1.906c-0.384-0.457-0.778-0.906-1.183-1.343
                c0.999-1.249,1.712-2.7,2.094-4.231c0.599,0.049,1.199,0.084,1.801,0.104c0.201-0.891,0.301-1.802,0.305-2.715
                C1199.378,951.83,1198.783,951.738,1198.187,951.664z M1190.503,959.17c-4.036,1.873-8.824,0.123-10.697-3.911
                c-1.875-4.035-0.121-8.825,3.914-10.698c4.035-1.872,8.824-0.121,10.696,3.914C1196.289,952.51,1194.538,957.299,1190.503,959.17z
                "/>
                <ellipse transform="matrix(-0.4212 -0.907 0.907 -0.4212 823.7584 2429.7207)" fill="#00ADBA" cx="1187.2" cy="952" rx="3.482" ry="3.523"/>
        </g>
        <polygon fill="#FAA21B" points="1158.017,1000.643 1156.904,999.531 1155.409,1003.209 1153.916,1006.888 1157.596,1005.396
            1161.273,1003.901 1160.16,1002.788 1162.676,1000.272 1160.533,998.126 	"/>
        <path fill="#008FAF" d="M1174.182,980.956c0,0-2.889,4.223-2.889,5.816c0,1.597,1.294,2.891,2.889,2.891
            c1.596,0,2.891-1.294,2.891-2.891C1177.073,985.178,1174.182,980.956,1174.182,980.956z"/>
        <path fill="#FEBC11" d="M1247.816,975.365c0.018-0.548-0.021-1.091-0.117-1.628c-0.359-0.002-0.715,0.005-1.072,0.024
            c-0.195-0.953-0.596-1.823-1.148-2.564c0.252-0.254,0.498-0.513,0.736-0.778c-0.334-0.43-0.717-0.823-1.137-1.173
            c-0.273,0.231-0.543,0.466-0.805,0.708c-0.736-0.581-1.596-1.003-2.527-1.227c0.031-0.355,0.051-0.713,0.063-1.07
            c-0.533-0.115-1.078-0.169-1.623-0.167c-0.064,0.353-0.117,0.708-0.162,1.061c-0.125,0.004-0.252,0.01-0.381,0.021
            c-0.84,0.075-1.633,0.307-2.344,0.662c-0.207-0.291-0.418-0.577-0.639-0.86c-0.482,0.256-0.936,0.566-1.352,0.916
            c0.18,0.312,0.365,0.616,0.557,0.917c-0.703,0.629-1.268,1.405-1.646,2.275c-0.344-0.093-0.691-0.173-1.041-0.25
            c-0.205,0.506-0.355,1.034-0.447,1.57c0.336,0.125,0.674,0.239,1.016,0.344c-0.072,0.494-0.09,1.003-0.041,1.52
            c0.037,0.443,0.121,0.872,0.242,1.283c-0.322,0.152-0.641,0.312-0.959,0.479c0.17,0.52,0.393,1.021,0.668,1.493
            c0.336-0.123,0.67-0.253,0.998-0.391c0.502,0.809,1.172,1.495,1.959,2.016c-0.152,0.325-0.293,0.65-0.426,0.982
            c0.459,0.291,0.953,0.527,1.467,0.713c0.178-0.307,0.352-0.621,0.514-0.938c0.824,0.27,1.711,0.382,2.629,0.303
            c0.055-0.007,0.111-0.011,0.166-0.018c0.094,0.346,0.195,0.687,0.307,1.027c0.537-0.074,1.07-0.208,1.584-0.396
            c-0.063-0.353-0.133-0.703-0.211-1.052c0.891-0.354,1.682-0.898,2.324-1.578c0.293,0.204,0.592,0.4,0.895,0.592
            c0.365-0.406,0.688-0.849,0.957-1.322c-0.271-0.232-0.553-0.455-0.836-0.671c0.441-0.818,0.711-1.734,0.77-2.701
            C1247.107,975.456,1247.461,975.414,1247.816,975.365z"/>
        <path fill="#F7941E" d="M1182.597,987.126c2.687,2.648,7.014,2.62,9.665-0.068c2.651-2.688,2.62-7.016-0.068-9.666
            c-2.687-2.648-7.014-2.62-9.665,0.066C1179.879,980.148,1179.908,984.473,1182.597,987.126z M1183.52,986.411l0.903-0.81
            l1.762-1.576c0.337,0.234,0.726,0.359,1.119,0.373l0.462,2.318l0.239,1.191C1186.417,988.077,1184.771,987.58,1183.52,986.411z
             M1191.44,986.25c-0.701,0.708-1.541,1.194-2.436,1.456l-0.237-1.188l-0.463-2.317c0.224-0.104,0.433-0.25,0.615-0.437
            c0.137-0.138,0.25-0.287,0.34-0.449l2.275,0.643l1.168,0.33C1192.431,985,1192.009,985.67,1191.44,986.25z M1192.979,983.305
            l-1.168-0.33l-2.275-0.644c0.013-0.369-0.069-0.744-0.25-1.08l1.761-1.578l0.906-0.807
            C1192.92,980.164,1193.264,981.78,1192.979,983.305z M1191.27,978.106l-0.902,0.81l-1.762,1.575
            c-0.337-0.23-0.725-0.354-1.118-0.373l-0.464-2.318l-0.239-1.188C1188.372,976.441,1190.02,976.937,1191.27,978.106z
             M1183.351,978.271c0.7-0.709,1.54-1.197,2.435-1.461l0.238,1.191l0.463,2.318c-0.223,0.104-0.434,0.248-0.617,0.434
            c-0.136,0.139-0.249,0.29-0.338,0.45l-2.275-0.644l-1.169-0.328C1182.359,979.519,1182.78,978.85,1183.351,978.271z
             M1182.98,981.542l2.273,0.641c-0.011,0.374,0.07,0.748,0.251,1.085l-1.761,1.575l-0.904,0.81c-0.97-1.297-1.313-2.915-1.027-4.438
            L1182.98,981.542z"/>
        <g>
            <polygon fill="#F6891F" points="1215.02,985.079 1209.992,986.115 1211.012,987.702 1207.094,990.222 1209.061,993.279
                1212.978,990.755 1213.998,992.338 1217.022,988.189 1220.049,984.041 		"/>
                <rect x="1204.541" y="991.664" transform="matrix(-0.5414 -0.8408 0.8408 -0.5414 1024.5012 2544.6577)" fill="#F6891F" width="3.483" height="2.484"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="1170.627,974.012 1173.399,971.924 1175.487,974.697 1174.66,975.319 1173.633,973.957
                1173.218,976.912 1172.193,976.765 1172.612,973.812 1171.248,974.839 		"/>
            <polygon fill="#AAD9B5" points="1174.375,970.703 1173.714,970.342 1174.749,968.432 1173.559,968.785 1173.344,968.063
                1175.77,967.346 1176.489,969.77 1175.766,969.985 1175.413,968.793 		"/>
        </g>
        <polygon fill="#F26522" points="1237.414,950.138 1234.477,950.138 1234.477,947.201 1232.432,947.201 1232.432,950.138
            1229.49,950.138 1229.49,952.184 1232.432,952.184 1232.432,955.122 1234.477,955.122 1234.477,952.184 1237.414,952.184 	"/>
        <polygon fill="#F5EB00" points="1156.134,972.944 1153.006,972.944 1153.006,969.813 1150.828,969.813 1150.828,972.944
            1147.699,972.944 1147.699,975.122 1150.828,975.122 1150.828,978.25 1153.006,978.25 1153.006,975.122 1156.134,975.122 	"/>
        <polygon fill="#8DC63F" points="1222.636,944.034 1224.285,941.987 1217.906,940.163 1211.527,938.338 1214.666,944.186
            1217.805,950.03 1219.457,947.981 1224.086,951.711 1227.268,947.763 	"/>
        <polygon fill="#4EA0B4" points="1203.678,944.934 1205.451,946.131 1201.869,951.443 1202.467,951.849 1206.051,946.536
            1207.827,947.733 1208.003,945.236 1208.177,942.74 	"/>
        <path fill="#00A0AF" d="M1192.375,999.906c-1.352-1.354-3.546-1.354-4.898,0c-1.353,1.352-1.353,3.544,0,4.898
            c1.353,1.352,3.547,1.352,4.898,0C1193.727,1003.45,1193.727,1001.257,1192.375,999.906z M1190.839,1003.267
            c-0.505,0.504-1.324,0.504-1.827,0c-0.504-0.503-0.504-1.322,0-1.826c0.503-0.504,1.322-0.504,1.827,0
            C1191.343,1001.945,1191.343,1002.762,1190.839,1003.267z"/>
        <polygon fill="#79AD36" points="1166.616,1013.083 1167.428,1010.631 1174.761,1013.061 1175.036,1012.234 1167.701,1009.802
            1168.512,1007.351 1165.558,1007.962 1162.603,1008.575 	"/>
        <polygon fill="#17AEB2" points="1204.307,984.152 1205.122,984.107 1204.976,981.354 1207.83,981.285 1204.324,975.535
            1201.098,981.448 1204.158,981.375 	"/>
        <g>
            <path fill="#BFE2CE" d="M1169.402,960.193l-4.007-0.666l0.667-4.006c0.069-0.402-0.204-0.783-0.606-0.849
                c-0.402-0.069-0.783,0.205-0.85,0.606l-0.667,4.006l-4.006-0.668c-0.402-0.065-0.783,0.206-0.849,0.607
                c-0.067,0.403,0.205,0.783,0.606,0.85l4.006,0.667l-0.667,4.006c-0.067,0.401,0.204,0.781,0.607,0.848
                c0.403,0.069,0.782-0.204,0.849-0.605l0.667-4.006l4.006,0.664c0.401,0.07,0.782-0.204,0.849-0.606
                C1170.078,960.64,1169.805,960.259,1169.402,960.193z"/>
            <path fill="#95A43A" d="M1173.208,960.125c0.499-0.077,0.999-0.172,1.496-0.277c-0.022-0.801-0.141-1.598-0.35-2.373
                c-0.507,0.039-1.013,0.089-1.516,0.158c-0.277-0.921-0.707-1.802-1.288-2.598c0.358-0.357,0.708-0.726,1.047-1.104
                c-0.49-0.636-1.057-1.211-1.68-1.712c-0.388,0.329-0.766,0.667-1.133,1.018c-0.796-0.605-1.666-1.052-2.567-1.343
                c0.079-0.5,0.145-1.004,0.198-1.51c-0.77-0.228-1.566-0.362-2.368-0.4c-0.12,0.495-0.225,0.992-0.316,1.491
                c-0.971-0.019-1.942,0.123-2.868,0.423c-0.228-0.451-0.471-0.896-0.725-1.337c-0.755,0.268-1.479,0.627-2.151,1.067
                c0.193,0.471,0.4,0.935,0.619,1.393c-0.715,0.496-1.366,1.109-1.924,1.834c-0.05,0.065-0.098,0.129-0.145,0.195
                c-0.451-0.23-0.909-0.449-1.374-0.657c-0.452,0.661-0.83,1.376-1.114,2.126c0.432,0.266,0.872,0.522,1.317,0.764
                c-0.323,0.928-0.481,1.895-0.479,2.858c-0.501,0.08-1.001,0.173-1.497,0.278c0.023,0.802,0.14,1.599,0.349,2.373
                c0.507-0.037,1.013-0.091,1.516-0.157c0.277,0.922,0.708,1.8,1.288,2.597c-0.358,0.356-0.706,0.725-1.046,1.104
                c0.488,0.636,1.057,1.211,1.68,1.714c0.387-0.33,0.765-0.67,1.132-1.021c0.796,0.607,1.666,1.055,2.568,1.347
                c-0.08,0.5-0.146,1.001-0.199,1.509c0.771,0.227,1.569,0.36,2.369,0.396c0.118-0.493,0.224-0.989,0.316-1.487
                c0.971,0.02,1.94-0.123,2.868-0.425c0.229,0.453,0.47,0.898,0.725,1.338c0.754-0.268,1.479-0.625,2.151-1.064
                c-0.192-0.47-0.398-0.936-0.619-1.394c0.715-0.495,1.367-1.109,1.923-1.835c0.051-0.063,0.099-0.13,0.146-0.196
                c0.451,0.23,0.909,0.45,1.373,0.658c0.452-0.661,0.829-1.375,1.114-2.126c-0.433-0.269-0.873-0.521-1.318-0.762
                C1173.049,962.057,1173.208,961.089,1173.208,960.125z"/>
            <g>
                <path fill="#A9BF38" d="M1163.665,965.433c2.927,0.488,5.694-1.489,6.182-4.417c0.486-2.927-1.49-5.694-4.418-6.182
                    c-2.926-0.487-5.694,1.492-6.182,4.418S1160.738,964.946,1163.665,965.433z M1165.196,956.23c2.155,0.36,3.612,2.397,3.253,4.552
                    c-0.358,2.157-2.396,3.613-4.552,3.252c-2.156-0.357-3.612-2.396-3.252-4.551C1161.004,957.329,1163.041,955.873,1165.196,956.23
                    z"/>
                <circle fill="#A9BF38" cx="1164.546" cy="960.134" r="2.002"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M1243.445,954.388c-3.033-0.016-5.502,2.431-5.518,5.466c-0.014,3.031,2.436,5.501,5.467,5.516
                c3.033,0.013,5.502-2.433,5.518-5.466C1248.924,956.872,1246.479,954.402,1243.445,954.388z M1243.402,963.921
                c-2.234-0.011-4.037-1.829-4.023-4.06c0.008-2.235,1.826-4.036,4.061-4.025c2.232,0.01,4.035,1.829,4.025,4.061
                C1247.453,962.13,1245.635,963.931,1243.402,963.921z"/>
            <circle fill="#CFB52B" cx="1243.42" cy="959.879" r="2.046"/>
        </g>
        <circle fill="#8DC63F" cx="1159.305" cy="985.643" r="2.804"/>
        <g>
            <path fill="#FEBC11" d="M1146.943,996.815c-0.451-0.313-0.715-0.707-0.792-1.187c-0.076-0.476,0.055-0.958,0.392-1.438
                c0.368-0.528,0.806-0.845,1.314-0.949c0.508-0.103,1.008,0.013,1.496,0.354c0.468,0.328,0.737,0.723,0.804,1.185
                c0.069,0.465-0.074,0.949-0.427,1.455c-0.363,0.521-0.797,0.834-1.302,0.938C1147.923,997.275,1147.428,997.156,1146.943,996.815z
                 M1153.805,996.833l-8.541,3.456l-1.078-0.753l8.533-3.461L1153.805,996.833z M1148.786,994.353
                c-0.43-0.3-0.854-0.151-1.271,0.447c-0.394,0.564-0.387,0.989,0.023,1.274c0.417,0.294,0.832,0.146,1.243-0.444
                C1149.18,995.057,1149.181,994.63,1148.786,994.353z M1148.647,1002.781c-0.452-0.317-0.715-0.712-0.791-1.19
                c-0.076-0.477,0.054-0.957,0.391-1.441c0.367-0.526,0.806-0.842,1.314-0.945c0.51-0.106,1.007,0.012,1.497,0.354
                c0.471,0.327,0.74,0.722,0.81,1.183c0.068,0.458-0.073,0.942-0.426,1.448c-0.363,0.521-0.799,0.836-1.307,0.939
                C1149.629,1003.233,1149.132,1003.117,1148.647,1002.781z M1150.476,1000.305c-0.428-0.298-0.85-0.147-1.266,0.45
                c-0.395,0.565-0.384,0.993,0.031,1.284c0.419,0.291,0.833,0.142,1.244-0.447c0.191-0.274,0.284-0.525,0.275-0.753
                C1150.754,1000.611,1150.658,1000.434,1150.476,1000.305z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M1232.793,987.444l0.602,0.914l-0.615,0.405l-0.584-0.89c-0.572,0.373-1.188,0.594-1.848,0.659
                l-0.766-1.164c0.25,0.022,0.572-0.015,0.971-0.116c0.395-0.098,0.727-0.222,0.988-0.37l-1.008-1.534
                c-0.814,0.188-1.439,0.218-1.865,0.091c-0.428-0.127-0.777-0.396-1.047-0.807c-0.289-0.439-0.381-0.918-0.273-1.435
                c0.107-0.514,0.4-0.966,0.881-1.354l-0.516-0.784l0.615-0.402l0.506,0.766c0.609-0.361,1.113-0.558,1.514-0.588l0.75,1.139
                c-0.553,0.02-1.09,0.165-1.617,0.436l1.049,1.595c0.762-0.183,1.369-0.22,1.824-0.11c0.453,0.11,0.814,0.368,1.08,0.771
                c0.307,0.466,0.406,0.94,0.293,1.42C1233.617,986.566,1233.307,987.019,1232.793,987.444z M1229.756,984.165l-0.879-1.335
                c-0.344,0.327-0.406,0.66-0.184,0.996C1228.885,984.115,1229.238,984.227,1229.756,984.165z M1231.307,985.184l0.838,1.275
                c0.361-0.324,0.43-0.658,0.207-0.999C1232.172,985.186,1231.82,985.094,1231.307,985.184z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M1221.977,968.6l-1.35-0.565l-1.002,2.391l-2.258-0.946l1.002-2.391l-4.902-2.054l0.662-1.576l7.768-5.298
                l2.438,1.024l-2.969,7.086l1.348,0.565L1221.977,968.6z M1221.07,960.729l-0.047-0.019c-0.152,0.155-0.414,0.389-0.791,0.699
                l-3.972,2.724l2.847,1.191l1.482-3.536C1220.721,961.475,1220.881,961.122,1221.07,960.729z"/>
        </g>
        <path fill="#262425" d="M1170.066,1062.94c-0.191,0.022-0.385,0.035-0.577,0.035c-2.384,0.002-4.399-1.784-4.69-4.151
            c-0.153-1.252,0.191-2.488,0.969-3.485c0.78-0.996,1.897-1.627,3.149-1.782c0.188-0.023,0.38-0.034,0.564-0.034l0.807-0.003
            l-0.275-2.266l-0.648,0.012c-0.236,0.004-0.48,0.02-0.723,0.052c-3.821,0.467-6.55,3.958-6.083,7.78
            c0.429,3.497,3.404,6.135,6.926,6.135c0.129,0,0.259-0.001,0.39-0.009c0.155-0.009,0.311-0.023,0.463-0.041
            c0.092-0.012,0.186-0.026,0.278-0.04v-2.302C1170.434,1062.884,1170.25,1062.917,1170.066,1062.94z"/>
        <path fill="#262425" d="M1170.315,1066.882c-0.189,0.025-0.383,0.042-0.578,0.052c-4.561,0.259-8.513-3.096-9.062-7.587
            c-0.58-4.738,2.801-9.062,7.536-9.643c0.193-0.021,0.39-0.038,0.58-0.051l1.025-0.047l-0.381-3.102l-0.821,0.044
            c-0.265,0.014-0.527,0.036-0.779,0.066c-6.439,0.79-11.037,6.668-10.25,13.108c0.722,5.891,5.738,10.336,11.667,10.336
            c0.217,0,0.437-0.006,0.658-0.021c0.235-0.012,0.471-0.034,0.707-0.061v-3.145
            C1170.515,1066.85,1170.418,1066.871,1170.315,1066.882z"/>
    </g>
    <g>
        <g>
            <path fill="#F26522" d="M1461.154,1105.13c0,2.236-0.945,3.322-2.887,3.322c-2.021,0-3.004-1.127-3.004-3.446v-7.496h-1.979v7.588
                c0,3.387,1.623,5.105,4.824,5.105c3.334,0,5.025-1.784,5.025-5.299v-7.395h-1.98V1105.13z"/>
            <path fill="#95A43A" d="M1467.525,1098.317l-1.943,0.598v2.029h-1.541v1.658h1.541v4.83c0,2.277,1.418,2.756,2.607,2.756
                c0.596,0,1.088-0.1,1.463-0.298l0.133-0.069v-1.808l-0.41,0.296c-0.219,0.16-0.475,0.234-0.781,0.234
                c-0.391,0-0.664-0.094-0.816-0.277c-0.164-0.194-0.252-0.569-0.252-1.077v-4.587h2.26v-1.658h-2.26V1098.317z"/>
            <path fill="#00A0AF" d="M1474.955,1100.743c-1.467,0-2.648,0.434-3.518,1.286c-0.867,0.854-1.307,2.047-1.307,3.545
                c0,1.382,0.424,2.509,1.256,3.353c0.838,0.847,1.967,1.277,3.357,1.277c1.428,0,2.584-0.44,3.438-1.312
                c0.85-0.866,1.281-2.026,1.281-3.451c0-1.454-0.4-2.61-1.189-3.439C1477.475,1101.167,1476.359,1100.743,1474.955,1100.743z
                 M1474.85,1108.562c-0.842,0-1.492-0.264-1.988-0.797c-0.498-0.538-0.75-1.291-0.75-2.242c0-0.989,0.252-1.771,0.744-2.322
                c0.49-0.55,1.143-0.817,1.994-0.817c0.857,0,1.492,0.258,1.943,0.783c0.457,0.533,0.689,1.314,0.689,2.323
                c0,0.997-0.232,1.77-0.689,2.296C1476.344,1108.309,1475.707,1108.562,1474.85,1108.562z"/>
            <path fill="#F26522" d="M1485.621,1100.743c-1.189,0-2.154,0.414-2.877,1.233v-1.032h-1.945v13.001h1.945v-4.744
                c0.639,0.665,1.465,1.003,2.467,1.003c1.344,0,2.414-0.464,3.193-1.384c0.764-0.902,1.148-2.114,1.148-3.597
                c0-1.341-0.346-2.43-1.027-3.24C1487.83,1101.16,1486.854,1100.743,1485.621,1100.743z M1487.574,1105.198
                c0,1.072-0.232,1.918-0.691,2.507c-0.445,0.576-1.047,0.857-1.84,0.857c-0.672,0-1.209-0.217-1.645-0.665
                c-0.441-0.452-0.654-0.986-0.654-1.637v-1.196c0-0.785,0.229-1.414,0.699-1.929c0.459-0.505,1.057-0.752,1.82-0.752
                c0.715,0,1.262,0.237,1.676,0.725C1487.361,1103.601,1487.574,1104.306,1487.574,1105.198z"/>
            <rect x="1490.889" y="1100.944" fill="#00A0AF" width="1.945" height="9.06"/>
            <path fill="#00A0AF" d="M1491.881,1096.983c-0.326,0-0.607,0.109-0.834,0.328c-0.232,0.216-0.352,0.488-0.352,0.812
                c0,0.325,0.119,0.599,0.352,0.812c0.227,0.212,0.51,0.32,0.834,0.32c0.328,0,0.615-0.11,0.848-0.329
                c0.234-0.221,0.35-0.489,0.35-0.803c0-0.323-0.117-0.598-0.352-0.813C1492.494,1097.092,1492.209,1096.983,1491.881,1096.983z"/>
            <path fill="#95A43A" d="M1498.293,1100.743c-1.193,0-2.264,0.283-3.182,0.842l-0.119,0.072v2.064l0.42-0.339
                c0.818-0.662,1.746-0.999,2.76-0.999c0.654,0,1.516,0.186,1.586,1.73l-2.463,0.33c-2.107,0.283-3.176,1.304-3.176,3.039
                c0,0.814,0.279,1.48,0.836,1.976c0.551,0.494,1.314,0.745,2.271,0.745c1.045,0,1.895-0.358,2.537-1.067v0.867h1.943v-5.821
                C1501.707,1101.933,1500.527,1100.743,1498.293,1100.743z M1499.764,1105.702v0.576c0,0.671-0.205,1.211-0.627,1.644
                c-0.418,0.432-0.936,0.641-1.578,0.641c-0.459,0-0.813-0.112-1.078-0.336c-0.258-0.22-0.383-0.496-0.383-0.844
                c0-0.494,0.129-0.822,0.398-1.003c0.305-0.202,0.773-0.349,1.395-0.432L1499.764,1105.702z"/>
        </g>
        <g>
            <path fill="#262425" d="M1490.984,1154.95c0,6.681-3.432,12.603-8.717,16.273c-1.064,10.199-10.15,18.166-21.205,18.166
                c-0.34,0-0.678-0.011-1.014-0.021c-2.68,2.721-6.146,4.742-10.055,5.737c-1.811,0.459-3.68,0.69-5.545,0.69
                c-2.801,0-5.477-0.511-7.924-1.441c-0.131,0.051-0.262,0.098-0.395,0.145c-0.004,0.002-0.008,0.004-0.016,0.009
                c-0.133,0.045-0.268,0.092-0.4,0.138l0,0c-2.225,0.745-4.619,1.15-7.111,1.15c-1.754,0-3.459-0.201-5.09-0.58
                c-1.914,0.84-4.047,1.309-6.295,1.309c-5.771,0-10.783-3.088-13.291-7.613c-4.996-0.375-9.211-3.376-11.102-7.547
                c-5.158-1.158-9.221-5.678-9.221-11.051c0-0.515,0.035-1.022,0.105-1.521c-1.928-1.873-3.322-4.276-3.92-6.902
                c-0.6-2.617-0.396-5.397,0.576-7.901c0.537-1.383,1.297-2.672,2.238-3.818c0.678-0.842,1.627-1.431,2.301-2.24
                c1.584-1.237,3.438-2.173,5.469-2.715c0.027-6.812,5.871-12.325,13.076-12.325c0.068,0,0.139,0,0.211,0.003
                c1.832-1.799,4.402-2.916,7.25-2.916c3.297,0,6.225,1.501,8.063,3.819c3.318-7.056,10.789-11.973,19.475-11.973
                c3.564,0,6.924,0.827,9.877,2.294c2.848-4.35,7.936-7.246,13.736-7.246c5.287,0,9.979,2.405,12.926,6.117
                c8.338,0.082,15.074,6.502,15.074,14.414c0,2.642-0.752,5.117-2.061,7.247C1489.896,1147.666,1490.984,1151.188,1490.984,1154.95z
                "/>
            <path fill="#00A0AF" d="M1430.621,1132.456c0.582,1.378,1.416,2.611,2.477,3.674c1.061,1.06,2.297,1.893,3.672,2.478
                c1.428,0.601,2.941,0.905,4.5,0.905H1471c4.76,0,8.635,3.875,8.635,8.638v3.608h2.922v-3.608c0-1.561-0.305-3.076-0.908-4.501
                c-0.58-1.376-1.416-2.611-2.477-3.676c-1.061-1.059-2.297-1.893-3.672-2.475c-1.426-0.602-2.939-0.91-4.5-0.91h-29.73
                c-4.76,0-8.635-3.872-8.635-8.633v-31.31h-2.922v31.31C1429.713,1129.515,1430.018,1131.029,1430.621,1132.456z"/>
            <path fill="#95A43A" d="M1435.371,1155.596c0.58,1.375,1.414,2.613,2.475,3.672c1.061,1.063,2.299,1.896,3.674,2.477
                c1.426,0.604,2.939,0.91,4.5,0.91h9.338v-2.925h-9.338c-4.76,0-8.635-3.872-8.635-8.632v-54.452h-2.924v54.452
                C1434.461,1152.656,1434.768,1154.17,1435.371,1155.596z"/>
            <path fill="#F7941E" d="M1412.965,1174.033v-40.876c0-4.762,3.871-8.638,8.633-8.638h8.938c1.561,0,3.072-0.304,4.5-0.907
                c1.377-0.583,2.611-1.416,3.672-2.477c1.063-1.062,1.896-2.296,2.479-3.672c0.602-1.426,0.908-2.94,0.908-4.501v-16.316h-2.924
                v16.316c0,4.761-3.873,8.636-8.635,8.636h-8.938c-1.561,0-3.074,0.306-4.5,0.907c-1.375,0.583-2.611,1.418-3.672,2.477
                c-1.063,1.063-1.895,2.297-2.479,3.674c-0.602,1.427-0.908,2.941-0.908,4.501v40.876H1412.965z"/>
            <path fill="#FEBC11" d="M1464.809,1158.432v-21.418c0-4.763-3.873-8.635-8.633-8.635h-0.768c-1.563,0-3.074-0.306-4.5-0.911
                c-1.379-0.581-2.613-1.414-3.674-2.477c-1.063-1.058-1.895-2.295-2.477-3.672c-0.604-1.426-0.91-2.938-0.91-4.497v-20.177h2.924
                v20.177c0,4.76,3.875,8.633,8.637,8.633h0.768c1.559,0,3.072,0.302,4.498,0.907c1.377,0.583,2.613,1.415,3.672,2.478
                c1.063,1.061,1.896,2.297,2.479,3.673c0.602,1.425,0.908,2.941,0.908,4.501v21.418H1464.809z"/>
            <path fill="#F26522" d="M1396.807,1157.497v-8.99c0-4.762,3.873-8.634,8.633-8.634l10.961,0.005c1.563,0,3.076-0.308,4.502-0.911
                c1.375-0.582,2.611-1.414,3.672-2.474c1.061-1.064,1.895-2.297,2.477-3.673c0.604-1.428,0.91-2.943,0.91-4.501v-31.673h-2.924
                v31.673c0,4.76-3.873,8.636-8.633,8.636l-1.426-0.007h-9.539c-1.559,0-3.072,0.307-4.498,0.908
                c-1.377,0.582-2.613,1.415-3.674,2.477c-1.063,1.062-1.895,2.297-2.477,3.675c-0.604,1.425-0.908,2.938-0.908,4.499v8.99H1396.807
                z"/>
            <path fill="#F7941E" d="M1430.467,1174.55c-3.063,1.897-7.369,3.081-12.141,3.081c-9.291,0-16.824-4.483-16.824-10.011
                c0-5.281,6.879-9.607,15.594-9.983c0.406-0.016,0.816-0.026,1.23-0.026c1.049,0,2.074,0.06,3.07,0.165
                c0.096-0.46,0.146-0.94,0.146-1.431c0-1.482-0.463-2.855-1.254-3.985c2.734,0.792,4.773,3.213,4.998,6.147
                c5.816,1.574,9.861,5.061,9.861,9.113C1435.148,1170.308,1433.365,1172.751,1430.467,1174.55z"/>
            <g>
                <path fill="#00ADBA" d="M1435.98,1179.731c0.031,1.402,0.346,2.815,0.973,4.166c0.037,0.08,0.076,0.162,0.115,0.241
                    c-0.479,0.335-0.947,0.68-1.41,1.035c0.408,0.78,0.904,1.515,1.475,2.188c0.512-0.288,1.01-0.589,1.502-0.902
                    c1.033,1.158,2.301,2.057,3.697,2.656c-0.154,0.559-0.293,1.119-0.418,1.687c0.822,0.328,1.678,0.571,2.555,0.719
                    c0.205-0.545,0.395-1.093,0.574-1.644c1.496,0.213,3.053,0.115,4.572-0.336c0.242,0.522,0.502,1.038,0.773,1.548
                    c0.848-0.275,1.664-0.638,2.434-1.08c-0.193-0.548-0.398-1.088-0.619-1.618c1.354-0.826,2.471-1.92,3.311-3.174
                    c0.525,0.242,1.061,0.471,1.6,0.688c0.477-0.752,0.873-1.551,1.18-2.382c-0.504-0.289-1.012-0.568-1.527-0.831
                    c0.488-1.438,0.672-2.982,0.506-4.522c0.563-0.152,1.121-0.317,1.678-0.5c-0.117-0.873-0.332-1.735-0.633-2.562
                    c-0.578,0.102-1.15,0.216-1.717,0.345c-0.076-0.189-0.156-0.377-0.244-0.563c-0.576-1.237-1.361-2.313-2.297-3.198
                    c0.334-0.475,0.656-0.958,0.971-1.449c-0.658-0.591-1.381-1.108-2.15-1.546c-0.379,0.445-0.746,0.896-1.102,1.358
                    c-1.352-0.721-2.852-1.146-4.398-1.237c-0.047-0.573-0.111-1.149-0.188-1.725c-0.889-0.029-1.781,0.035-2.66,0.195
                    c-0.004,0.581,0.004,1.158,0.029,1.734c-0.795,0.169-1.586,0.426-2.357,0.782c-0.66,0.307-1.275,0.671-1.84,1.085
                    c-0.408-0.404-0.83-0.803-1.262-1.189c-0.707,0.544-1.354,1.165-1.928,1.844c0.371,0.446,0.754,0.879,1.146,1.304
                    c-0.969,1.208-1.658,2.611-2.027,4.095c-0.578-0.046-1.16-0.08-1.744-0.102c-0.193,0.864-0.293,1.746-0.295,2.63
                    C1434.83,1179.573,1435.404,1179.661,1435.98,1179.731z M1443.422,1172.461c3.906-1.812,8.543-0.115,10.357,3.791
                    s0.115,8.543-3.791,10.356c-3.904,1.814-8.541,0.118-10.355-3.79C1437.816,1178.913,1439.516,1174.276,1443.422,1172.461z"/>
                    <ellipse transform="matrix(0.4212 0.9069 -0.9069 0.4212 1906.9021 -629.4174)" fill="#00ADBA" cx="1446.619" cy="1179.408" rx="3.371" ry="3.411"/>
            </g>
            <polygon fill="#FAA21B" points="1475.227,1132.217 1476.498,1133.063 1477.213,1129.286 1477.928,1125.504 1474.721,1127.629
                1471.516,1129.749 1472.783,1130.589 1470.875,1133.458 1473.32,1135.085 		"/>
            <path fill="#008FAF" d="M1459.223,1142.941c0,0-2.797,4.085-2.797,5.63s1.254,2.799,2.797,2.799c1.547,0,2.801-1.254,2.801-2.799
                S1459.223,1142.941,1459.223,1142.941z"/>
            <path fill="#FEBC11" d="M1387.926,1156.786c-0.016,0.526,0.02,1.057,0.115,1.573c0.348,0,0.691-0.005,1.037-0.021
                c0.189,0.92,0.578,1.762,1.111,2.48c-0.242,0.244-0.479,0.497-0.711,0.753c0.324,0.418,0.693,0.798,1.1,1.138
                c0.266-0.225,0.525-0.45,0.777-0.687c0.715,0.562,1.547,0.973,2.447,1.189c-0.027,0.343-0.047,0.688-0.061,1.033
                c0.518,0.112,1.045,0.164,1.574,0.16c0.061-0.341,0.111-0.682,0.156-1.025c0.121-0.003,0.244-0.01,0.367-0.02
                c0.814-0.073,1.58-0.296,2.271-0.642c0.197,0.281,0.404,0.56,0.617,0.833c0.467-0.246,0.906-0.546,1.309-0.888
                c-0.172-0.3-0.354-0.599-0.539-0.887c0.682-0.608,1.229-1.361,1.594-2.203c0.334,0.089,0.672,0.169,1.01,0.241
                c0.197-0.489,0.344-1.003,0.434-1.523c-0.326-0.117-0.654-0.23-0.984-0.334c0.07-0.475,0.086-0.967,0.041-1.468
                c-0.039-0.427-0.119-0.845-0.236-1.243c0.313-0.146,0.621-0.3,0.928-0.465c-0.162-0.501-0.381-0.985-0.645-1.44
                c-0.328,0.115-0.648,0.242-0.969,0.374c-0.484-0.781-1.133-1.446-1.895-1.951c0.146-0.313,0.285-0.631,0.414-0.95
                c-0.447-0.281-0.924-0.512-1.424-0.69c-0.172,0.297-0.34,0.602-0.498,0.908c-0.795-0.264-1.656-0.37-2.545-0.292
                c-0.053,0.003-0.105,0.009-0.16,0.014c-0.09-0.333-0.189-0.663-0.297-0.992c-0.521,0.071-1.037,0.202-1.531,0.386
                c0.059,0.34,0.125,0.679,0.201,1.016c-0.863,0.344-1.627,0.868-2.25,1.528c-0.285-0.198-0.572-0.388-0.867-0.572
                c-0.352,0.392-0.664,0.821-0.926,1.279c0.266,0.225,0.535,0.442,0.809,0.65c-0.426,0.792-0.688,1.68-0.744,2.613
                C1388.615,1156.698,1388.271,1156.735,1387.926,1156.786z"/>
            <path fill="#F7941E" d="M1451.078,1145.396c-2.602-2.567-6.791-2.537-9.359,0.063c-2.566,2.602-2.535,6.794,0.064,9.357
                c2.602,2.569,6.793,2.541,9.359-0.063C1453.709,1152.153,1453.68,1147.964,1451.078,1145.396z M1450.184,1146.086l-0.877,0.782
                l-1.705,1.526c-0.326-0.224-0.701-0.345-1.082-0.357l-0.447-2.246l-0.232-1.151
                C1447.377,1144.474,1448.973,1144.955,1450.184,1146.086z M1442.516,1146.247c0.678-0.688,1.49-1.158,2.355-1.413l0.232,1.149
                l0.447,2.245c-0.217,0.103-0.418,0.243-0.596,0.423c-0.133,0.134-0.242,0.28-0.33,0.436l-2.203-0.623l-1.131-0.317
                C1441.557,1147.455,1441.963,1146.806,1442.516,1146.247z M1441.025,1149.094l1.131,0.318l2.203,0.623
                c-0.014,0.36,0.068,0.722,0.24,1.049l-1.705,1.525l-0.875,0.782C1441.08,1152.139,1440.75,1150.571,1441.025,1149.094z
                 M1442.68,1154.127l0.875-0.782l1.705-1.524c0.326,0.225,0.701,0.346,1.082,0.359l0.451,2.243l0.229,1.152
                C1445.484,1155.744,1443.891,1155.261,1442.68,1154.127z M1450.348,1153.969c-0.678,0.688-1.492,1.159-2.357,1.413l-0.23-1.151
                l-0.449-2.246c0.217-0.101,0.42-0.239,0.598-0.42c0.131-0.132,0.24-0.279,0.326-0.435l2.205,0.622l1.131,0.318
                C1451.307,1152.762,1450.9,1153.411,1450.348,1153.969z M1450.707,1150.801l-2.203-0.619c0.012-0.362-0.068-0.725-0.242-1.051
                l1.705-1.525l0.877-0.785c0.938,1.257,1.268,2.822,0.994,4.302L1450.707,1150.801z"/>
            <g>
                <polygon fill="#F6891F" points="1424.893,1140.78 1420.021,1141.785 1421.01,1143.321 1417.217,1145.763 1419.123,1148.721
                    1422.916,1146.28 1423.904,1147.813 1426.832,1143.793 1429.762,1139.779 			"/>
                    <rect x="1414.747" y="1147.157" transform="matrix(-0.5414 -0.8408 0.8408 -0.5414 1217.7594 2960.9653)" fill="#F6891F" width="3.37" height="2.406"/>
            </g>
            <g>
                <polygon fill="#AAD9B5" points="1456.387,1160.731 1459.07,1158.711 1461.092,1161.396 1460.291,1161.997 1459.299,1160.677
                    1458.896,1163.539 1457.904,1163.398 1458.309,1160.539 1456.986,1161.532 			"/>
                <polygon fill="#AAD9B5" points="1460.016,1157.528 1459.373,1157.18 1460.379,1155.33 1459.225,1155.671 1459.018,1154.971
                    1461.365,1154.276 1462.063,1156.625 1461.363,1156.832 1461.021,1155.678 			"/>
            </g>
            <polygon fill="#F26522" points="1397.998,1181.21 1400.844,1181.21 1400.844,1184.053 1402.824,1184.053 1402.824,1181.21
                1405.668,1181.21 1405.668,1179.231 1402.824,1179.231 1402.824,1176.382 1400.844,1176.382 1400.844,1179.231 1397.998,1179.231
                        "/>
            <polygon fill="#F5EB00" points="1476.699,1159.128 1479.729,1159.128 1479.729,1162.158 1481.84,1162.158 1481.84,1159.128
                1484.869,1159.128 1484.869,1157.019 1481.84,1157.019 1481.84,1153.992 1479.729,1153.992 1479.729,1157.019 1476.699,1157.019
                        "/>
            <polygon fill="#8DC63F" points="1412.309,1187.119 1410.711,1189.104 1416.889,1190.868 1423.063,1192.634 1420.023,1186.973
                1416.984,1181.314 1415.387,1183.299 1410.904,1179.688 1407.824,1183.506 		"/>
            <polygon fill="#4EA0B4" points="1430.666,1186.25 1428.947,1185.088 1432.418,1179.946 1431.838,1179.555 1428.367,1184.699
                1426.648,1183.539 1426.479,1185.955 1426.309,1188.373 		"/>
            <path fill="#00A0AF" d="M1441.609,1133.022c1.311,1.31,3.434,1.31,4.744,0c1.309-1.309,1.309-3.434,0-4.743
                c-1.311-1.31-3.434-1.31-4.744,0S1440.299,1131.713,1441.609,1133.022z M1443.096,1129.766c0.488-0.487,1.281-0.487,1.768,0
                c0.488,0.488,0.488,1.281,0,1.769c-0.486,0.487-1.279,0.487-1.768,0C1442.609,1131.047,1442.609,1130.254,1443.096,1129.766z"/>
            <polygon fill="#79AD36" points="1462.35,1124.899 1464.584,1123.774 1467.945,1130.461 1468.697,1130.081 1465.34,1123.398
                1467.574,1122.271 1465.131,1120.667 1462.689,1119.063 		"/>
            <polygon fill="#17AEB2" points="1430.055,1148.276 1429.266,1148.317 1429.408,1150.985 1426.645,1151.052 1430.039,1156.618
                1433.164,1150.893 1430.199,1150.966 		"/>
            <g>
                <path fill="#BFE2CE" d="M1463.854,1171.472l3.879,0.648l-0.645,3.879c-0.066,0.388,0.197,0.757,0.586,0.821
                    c0.391,0.066,0.76-0.198,0.822-0.587l0.646-3.879l3.879,0.644c0.389,0.067,0.758-0.195,0.822-0.586
                    c0.064-0.389-0.197-0.758-0.588-0.823l-3.879-0.645l0.646-3.881c0.064-0.386-0.199-0.757-0.59-0.821
                    c-0.389-0.066-0.756,0.197-0.82,0.587l-0.646,3.879l-3.879-0.646c-0.391-0.063-0.756,0.2-0.822,0.588
                    C1463.201,1171.04,1463.463,1171.409,1463.854,1171.472z"/>
                <path fill="#95A43A" d="M1460.17,1171.538c-0.484,0.079-0.967,0.169-1.449,0.27c0.023,0.775,0.135,1.547,0.338,2.298
                    c0.492-0.039,0.982-0.089,1.467-0.153c0.27,0.894,0.688,1.744,1.248,2.516c-0.346,0.347-0.686,0.705-1.014,1.069
                    c0.475,0.615,1.023,1.174,1.627,1.658c0.375-0.317,0.74-0.647,1.096-0.985c0.771,0.586,1.613,1.018,2.486,1.302
                    c-0.076,0.484-0.141,0.971-0.191,1.46c0.744,0.219,1.518,0.35,2.293,0.387c0.115-0.478,0.219-0.96,0.307-1.443
                    c0.939,0.019,1.879-0.117,2.775-0.411c0.223,0.438,0.457,0.871,0.701,1.296c0.732-0.259,1.434-0.605,2.086-1.032
                    c-0.189-0.454-0.389-0.904-0.6-1.346c0.691-0.479,1.322-1.074,1.863-1.778c0.047-0.063,0.094-0.124,0.141-0.189
                    c0.434,0.225,0.879,0.436,1.328,0.636c0.439-0.64,0.803-1.333,1.078-2.06c-0.418-0.258-0.846-0.504-1.275-0.737
                    c0.313-0.9,0.467-1.836,0.467-2.767c0.482-0.077,0.967-0.168,1.445-0.271c-0.021-0.774-0.133-1.548-0.336-2.299
                    c-0.492,0.036-0.98,0.09-1.469,0.153c-0.266-0.893-0.686-1.743-1.246-2.514c0.346-0.346,0.686-0.705,1.014-1.069
                    c-0.475-0.617-1.023-1.175-1.627-1.66c-0.375,0.322-0.74,0.649-1.098,0.986c-0.77-0.585-1.613-1.019-2.484-1.301
                    c0.076-0.483,0.141-0.97,0.191-1.462c-0.746-0.219-1.518-0.349-2.295-0.386c-0.113,0.478-0.215,0.96-0.305,1.442
                    c-0.939-0.02-1.879,0.121-2.777,0.411c-0.221-0.437-0.455-0.868-0.701-1.296c-0.73,0.261-1.434,0.607-2.084,1.033
                    c0.188,0.455,0.389,0.904,0.602,1.347c-0.693,0.481-1.322,1.075-1.863,1.776c-0.049,0.064-0.096,0.127-0.143,0.191
                    c-0.436-0.226-0.877-0.438-1.328-0.637c-0.439,0.64-0.803,1.332-1.08,2.058c0.42,0.261,0.846,0.505,1.275,0.738
                    C1460.32,1169.669,1460.17,1170.606,1460.17,1171.538z"/>
                <g>
                    <path fill="#A9BF38" d="M1469.408,1166.401c-2.834-0.474-5.516,1.442-5.986,4.277s1.441,5.513,4.279,5.984
                        c2.832,0.473,5.512-1.443,5.986-4.277C1474.158,1169.55,1472.242,1166.874,1469.408,1166.401z M1467.926,1175.31
                        c-2.086-0.348-3.496-2.323-3.15-4.409c0.348-2.086,2.322-3.494,4.408-3.148c2.088,0.348,3.496,2.321,3.15,4.409
                        C1471.986,1174.248,1470.012,1175.659,1467.926,1175.31z"/>
                    <path fill="#A9BF38" d="M1468.873,1169.619c-1.059-0.177-2.055,0.537-2.232,1.595c-0.174,1.056,0.539,2.057,1.596,2.232
                        c1.055,0.172,2.055-0.54,2.23-1.595C1470.643,1170.793,1469.93,1169.794,1468.873,1169.619z"/>
                </g>
            </g>
            <g>
                <path fill="#BFB131" d="M1392.16,1177.093c2.934,0.015,5.326-2.353,5.342-5.289c0.012-2.937-2.357-5.33-5.293-5.344
                    c-2.936-0.013-5.328,2.356-5.342,5.291C1386.854,1174.688,1389.223,1177.082,1392.16,1177.093z M1392.201,1167.863
                    c2.162,0.011,3.906,1.772,3.896,3.934c-0.01,2.163-1.771,3.907-3.932,3.897c-2.164-0.012-3.908-1.772-3.896-3.934
                    C1388.277,1169.598,1390.041,1167.853,1392.201,1167.863z"/>
                <path fill="#CFB52B" d="M1392.174,1173.76c1.096,0.006,1.986-0.879,1.992-1.974c0.004-1.094-0.879-1.985-1.973-1.99
                    c-1.094-0.006-1.986,0.878-1.99,1.973C1390.197,1172.861,1391.08,1173.756,1392.174,1173.76z"/>
            </g>
            <path fill="#8DC63F" d="M1470.912,1146.833c0-1.501,1.217-2.718,2.719-2.718c1.498,0,2.715,1.217,2.715,2.718
                c0,1.498-1.217,2.714-2.715,2.714C1472.129,1149.546,1470.912,1148.331,1470.912,1146.833z"/>
            <g>
                <path fill="#FEBC11" d="M1485.6,1136.014c0.438,0.301,0.691,0.686,0.766,1.147s-0.053,0.928-0.379,1.396
                    c-0.357,0.51-0.779,0.817-1.271,0.918s-0.977-0.015-1.449-0.342c-0.453-0.319-0.713-0.699-0.779-1.148
                    c-0.064-0.449,0.07-0.918,0.412-1.409c0.354-0.504,0.773-0.806,1.264-0.905C1484.65,1135.571,1485.131,1135.683,1485.6,1136.014z
                     M1478.955,1135.998l8.271-3.347l1.043,0.729l-8.262,3.353L1478.955,1135.998z M1483.816,1138.397
                    c0.418,0.291,0.824,0.146,1.23-0.433c0.383-0.549,0.373-0.959-0.023-1.235c-0.402-0.282-0.807-0.141-1.203,0.433
                    C1483.434,1137.715,1483.432,1138.128,1483.816,1138.397z M1483.951,1130.241c0.436,0.301,0.689,0.686,0.766,1.149
                    c0.072,0.461-0.053,0.927-0.381,1.395c-0.355,0.51-0.779,0.816-1.271,0.918s-0.977-0.014-1.447-0.345
                    c-0.457-0.316-0.721-0.697-0.785-1.143c-0.068-0.446,0.068-0.914,0.412-1.402c0.352-0.505,0.773-0.81,1.264-0.912
                    C1483,1129.797,1483.48,1129.912,1483.951,1130.241z M1482.178,1132.634c0.414,0.288,0.822,0.142,1.227-0.438
                    c0.383-0.544,0.373-0.96-0.029-1.241c-0.404-0.281-0.807-0.137-1.205,0.433c-0.186,0.266-0.273,0.509-0.266,0.729
                    C1481.91,1132.336,1482.002,1132.511,1482.178,1132.634z"/>
            </g>
            <g>
                <path fill="#95A43A" d="M1402.473,1145.086l-0.582-0.884l0.594-0.394l0.568,0.863c0.553-0.362,1.15-0.574,1.785-0.64l0.744,1.128
                    c-0.24-0.023-0.555,0.015-0.938,0.111s-0.703,0.217-0.959,0.362l0.977,1.484c0.791-0.185,1.393-0.213,1.807-0.089
                    c0.414,0.123,0.752,0.383,1.014,0.779c0.279,0.426,0.367,0.891,0.266,1.388c-0.105,0.499-0.391,0.938-0.855,1.313l0.498,0.756
                    l-0.594,0.395l-0.488-0.745c-0.592,0.351-1.08,0.538-1.467,0.57l-0.725-1.101c0.533-0.021,1.053-0.159,1.563-0.421l-1.016-1.549
                    c-0.738,0.18-1.324,0.216-1.764,0.109c-0.439-0.109-0.789-0.355-1.045-0.748c-0.297-0.451-0.393-0.91-0.287-1.373
                    C1401.676,1145.936,1401.977,1145.499,1402.473,1145.086z M1405.414,1148.263l0.852,1.29c0.332-0.313,0.391-0.634,0.176-0.963
                    C1406.258,1148.313,1405.916,1148.202,1405.414,1148.263z M1403.914,1147.277l-0.814-1.237c-0.348,0.316-0.414,0.64-0.199,0.968
                    C1403.076,1147.273,1403.412,1147.363,1403.914,1147.277z"/>
            </g>
            <g>
                <path fill="#FEBC11" d="M1418.566,1173.04l-1.309-0.546l-0.971,2.315l-2.186-0.916l0.971-2.315l-4.744-1.987l0.639-1.525
                    l7.521-5.131l2.361,0.988l-2.877,6.862l1.309,0.55L1418.566,1173.04z M1417.689,1165.419l-0.045-0.019
                    c-0.148,0.15-0.404,0.378-0.766,0.677l-3.848,2.636l2.756,1.154l1.436-3.422
                    C1417.35,1166.141,1417.506,1165.801,1417.689,1165.419z"/>
            </g>
        </g>
        <g>
            <path fill="#262425" d="M1430.211,1082.985l-0.244-0.1c-0.25-0.104-0.58-0.153-1.012-0.153c-0.615,0-1.178,0.21-1.672,0.627
                c-0.117,0.095-0.227,0.204-0.328,0.323v-0.79h-2.234v9.806h2.234v-4.992c0-0.915,0.184-1.638,0.547-2.147
                c0.34-0.477,0.74-0.707,1.225-0.707c0.383,0,0.672,0.069,0.852,0.209l0.633,0.489V1082.985z"/>
            <path fill="#262425" d="M1435.705,1082.678c-1.514,0-2.738,0.47-3.639,1.396c-0.898,0.926-1.354,2.211-1.354,3.824
                c0,1.49,0.438,2.709,1.301,3.623c0.869,0.921,2.039,1.386,3.479,1.386c1.477,0,2.674-0.478,3.561-1.423
                c0.879-0.938,1.326-2.192,1.326-3.726c0-1.563-0.416-2.81-1.234-3.71C1438.316,1083.141,1437.158,1082.678,1435.705,1082.678z
                 M1437.447,1090.138c-0.426,0.516-1.029,0.765-1.848,0.765c-0.805,0-1.426-0.257-1.896-0.793
                c-0.479-0.535-0.719-1.299-0.719-2.263c0-1.008,0.24-1.799,0.713-2.353c0.467-0.545,1.088-0.811,1.902-0.811
                c0.816,0,1.42,0.254,1.848,0.771c0.441,0.535,0.664,1.328,0.664,2.356C1438.111,1088.827,1437.889,1089.611,1437.447,1090.138z"/>
            <polygon fill="#262425" points="1451.998,1082.892 1450.291,1089.224 1448.549,1082.892 1446.637,1082.892 1444.684,1089.247
                1442.932,1082.892 1440.584,1082.892 1443.555,1092.698 1445.582,1092.698 1447.477,1086.614 1449.229,1092.698
                1451.316,1092.698 1454.256,1082.892 		"/>
            <path fill="#262425" d="M1461.332,1083.473c-0.605-0.526-1.389-0.795-2.344-0.795c-1.367,0-2.479,0.501-3.307,1.488
                c-0.809,0.97-1.219,2.259-1.219,3.839c0,1.481,0.373,2.676,1.109,3.551c0.754,0.897,1.77,1.352,3.02,1.352
                c1.119,0,2.037-0.345,2.74-1.024v0.814h2.232v-14.141h-2.232V1083.473z M1460.674,1090.168c-0.426,0.493-0.969,0.734-1.658,0.734
                c-0.697,0-1.236-0.247-1.645-0.757c-0.424-0.525-0.639-1.269-0.639-2.212c0-1.041,0.227-1.862,0.674-2.434
                c0.43-0.549,1.008-0.816,1.768-0.816c0.633,0,1.139,0.209,1.545,0.634c0.412,0.434,0.613,0.95,0.613,1.576v1.331
                C1461.332,1089.022,1461.115,1089.658,1460.674,1090.168z"/>
            <path fill="#262425" d="M1471.781,1088.784c-0.143-0.314-0.35-0.591-0.609-0.829c-0.25-0.225-0.551-0.43-0.896-0.604
                c-0.326-0.168-0.699-0.33-1.107-0.488c-0.297-0.115-0.561-0.222-0.791-0.322c-0.207-0.086-0.379-0.182-0.518-0.286
                c-0.117-0.089-0.205-0.186-0.266-0.294c-0.051-0.097-0.078-0.234-0.078-0.401c0-0.121,0.025-0.229,0.078-0.327
                c0.057-0.104,0.139-0.192,0.244-0.268c0.119-0.085,0.268-0.152,0.438-0.202c0.182-0.051,0.391-0.077,0.625-0.077
                c0.779,0,1.477,0.198,2.072,0.587l0.609,0.4v-2.449l-0.234-0.105c-0.662-0.29-1.424-0.438-2.256-0.438
                c-0.471,0-0.938,0.062-1.383,0.183c-0.455,0.122-0.867,0.309-1.225,0.554c-0.369,0.251-0.67,0.567-0.893,0.941
                c-0.23,0.387-0.346,0.832-0.346,1.324c0,0.396,0.061,0.749,0.184,1.053c0.119,0.309,0.305,0.584,0.543,0.826
                c0.232,0.229,0.518,0.436,0.852,0.615c0.313,0.166,0.68,0.333,1.096,0.496c0.285,0.113,0.551,0.22,0.799,0.322
                c0.223,0.091,0.416,0.193,0.576,0.306c0.141,0.099,0.254,0.21,0.334,0.336c0.066,0.103,0.1,0.231,0.1,0.396
                c0,0.261,0,0.872-1.506,0.872c-0.867,0-1.641-0.26-2.365-0.794l-0.629-0.466v2.572l0.211,0.11
                c0.734,0.387,1.607,0.582,2.598,0.582c0.496,0,0.982-0.058,1.441-0.168c0.471-0.117,0.895-0.3,1.26-0.54
                c0.375-0.248,0.682-0.566,0.908-0.945c0.232-0.391,0.352-0.85,0.352-1.366C1471.998,1089.471,1471.924,1089.098,1471.781,1088.784
                z"/>
            <path fill="#262425" d="M1477.775,1093.262c-1.424,0-2.563-0.452-3.41-1.353c-0.854-0.899-1.277-2.096-1.277-3.582
                c0-1.618,0.443-2.885,1.328-3.796c0.885-0.908,2.08-1.362,3.586-1.362c1.436,0,2.559,0.442,3.365,1.327
                c0.805,0.883,1.209,2.11,1.209,3.681c0,1.537-0.436,2.769-1.303,3.696C1480.404,1092.797,1479.238,1093.262,1477.775,1093.262z
                 M1477.889,1084.467c-0.992,0-1.777,0.338-2.354,1.013c-0.578,0.675-0.865,1.605-0.865,2.791c0,1.145,0.291,2.042,0.873,2.701
                c0.586,0.661,1.367,0.987,2.346,0.987c0.998,0,1.764-0.322,2.301-0.969c0.537-0.645,0.807-1.564,0.807-2.756
                c0-1.206-0.27-2.135-0.807-2.788S1478.887,1084.467,1477.889,1084.467z"/>
            <path fill="#262425" d="M1492.355,1093.035h-1.545v-1.524h-0.037c-0.639,1.166-1.633,1.752-2.975,1.752
                c-2.297,0-3.443-1.369-3.443-4.107v-5.76h1.533v5.518c0,2.031,0.775,3.047,2.334,3.047c0.754,0,1.373-0.277,1.859-0.831
                c0.484-0.557,0.729-1.282,0.729-2.18v-5.554h1.545V1093.035z"/>
            <path fill="#262425" d="M1500.014,1084.958c-0.268-0.209-0.656-0.311-1.166-0.311c-0.658,0-1.209,0.311-1.652,0.932
                c-0.441,0.622-0.662,1.469-0.662,2.543v4.913h-1.543v-9.64h1.543v1.984h0.037c0.219-0.677,0.555-1.203,1.006-1.584
                c0.453-0.38,0.959-0.569,1.518-0.569c0.4,0,0.709,0.043,0.92,0.133V1084.958z"/>
            <path fill="#262425" d="M1507.781,1092.591c-0.742,0.445-1.619,0.671-2.637,0.671c-1.375,0-2.484-0.45-3.328-1.345
                s-1.268-2.052-1.268-3.477c0-1.589,0.457-2.862,1.367-3.827c0.908-0.963,2.125-1.444,3.643-1.444c0.846,0,1.592,0.157,2.24,0.47
                v1.582c-0.717-0.503-1.482-0.754-2.299-0.754c-0.984,0-1.791,0.354-2.422,1.063c-0.631,0.705-0.945,1.631-0.945,2.777
                c0,1.131,0.297,2.021,0.887,2.676c0.596,0.652,1.391,0.977,2.387,0.977c0.844,0,1.633-0.279,2.375-0.836V1092.591z"/>
            <path fill="#262425" d="M1510.422,1080.946c-0.275,0-0.51-0.093-0.703-0.281c-0.195-0.188-0.293-0.426-0.293-0.714
                c0-0.29,0.098-0.528,0.293-0.721c0.193-0.191,0.428-0.288,0.703-0.288c0.283,0,0.523,0.097,0.721,0.288
                c0.199,0.192,0.297,0.431,0.297,0.721c0,0.275-0.098,0.511-0.297,0.705C1510.945,1080.849,1510.705,1080.946,1510.422,1080.946z
                 M1511.174,1093.035h-1.543v-9.64h1.543V1093.035z"/>
            <path fill="#262425" d="M1521.82,1093.035h-1.543v-5.499c0-2.043-0.748-3.068-2.242-3.068c-0.77,0-1.41,0.292-1.914,0.871
                c-0.506,0.581-0.758,1.313-0.758,2.197v5.499h-1.543v-9.64h1.543v1.601h0.037c0.729-1.218,1.783-1.826,3.162-1.826
                c1.057,0,1.861,0.34,2.42,1.021c0.559,0.682,0.838,1.666,0.838,2.952V1093.035z"/>
            <path fill="#262425" d="M1532.475,1092.263c0,3.538-1.693,5.31-5.084,5.31c-1.191,0-2.232-0.228-3.125-0.68v-1.543
                c1.086,0.602,2.121,0.905,3.107,0.905c2.373,0,3.557-1.263,3.557-3.787v-1.053h-0.037c-0.734,1.229-1.84,1.847-3.314,1.847
                c-1.197,0-2.162-0.431-2.893-1.286c-0.732-0.856-1.098-2.009-1.098-3.453c0-1.636,0.393-2.937,1.184-3.905
                c0.785-0.964,1.863-1.448,3.232-1.448c1.299,0,2.262,0.522,2.889,1.562h0.037v-1.336h1.545V1092.263z M1530.93,1088.674v-1.42
                c0-0.766-0.258-1.422-0.775-1.966c-0.518-0.549-1.162-0.821-1.936-0.821c-0.953,0-1.699,0.347-2.238,1.041
                c-0.541,0.694-0.811,1.663-0.811,2.912c0,1.076,0.26,1.935,0.775,2.577c0.52,0.644,1.203,0.962,2.059,0.962
                c0.865,0,1.57-0.305,2.113-0.922C1530.66,1090.423,1530.93,1089.634,1530.93,1088.674z"/>
            <path fill="#95A43A" d="M1421.701,1083.058c-0.25,0.029-0.504,0.046-0.754,0.046c-3.113,0-5.744-2.331-6.125-5.421
                c-0.199-1.636,0.252-3.252,1.268-4.554c1.018-1.299,2.479-2.127,4.111-2.325c0.246-0.03,0.494-0.048,0.736-0.048l1.057-0.005
                l-0.361-2.957l-0.846,0.017c-0.311,0.005-0.627,0.028-0.945,0.065c-4.992,0.612-8.555,5.172-7.945,10.161
                c0.559,4.569,4.449,8.017,9.047,8.017c0.17,0,0.338-0.006,0.51-0.019c0.201-0.009,0.404-0.027,0.604-0.051
                c0.121-0.016,0.242-0.034,0.361-0.056v-3.003C1422.182,1082.983,1421.939,1083.027,1421.701,1083.058z"/>
            <path fill="#F26522" d="M1422.025,1088.206c-0.244,0.03-0.5,0.054-0.754,0.066c-5.957,0.338-11.117-4.038-11.836-9.91
                c-0.758-6.186,3.66-11.831,9.842-12.587c0.252-0.032,0.512-0.056,0.76-0.07l1.338-0.059l-0.496-4.053l-1.074,0.054
                c-0.346,0.021-0.688,0.053-1.02,0.09c-8.408,1.032-14.412,8.709-13.385,17.118c0.943,7.694,7.494,13.5,15.236,13.5
                c0.285,0,0.572-0.009,0.859-0.024c0.309-0.017,0.619-0.045,0.922-0.082v-4.106
                C1422.285,1088.164,1422.158,1088.19,1422.025,1088.206z"/>
        </g>
    </g>
    <g>
        <g>
            <path fill="#262425" d="M1381.988,866.739c-4.969,0.609-8.518,5.146-7.91,10.117c0.578,4.729,4.758,8.229,9.512,7.962l0,0
                c0.203-0.011,0.404-0.029,0.605-0.053c0.309-0.038,0.619-0.093,0.926-0.163l0.395-0.09l-0.248-2.028l-0.49,0.119
                c-0.273,0.067-0.555,0.118-0.828,0.151c-3.881,0.474-7.391-2.313-7.859-6.146c-0.475-3.86,2.285-7.388,6.145-7.86
                c0.283-0.034,0.563-0.051,0.842-0.052l0.504-0.001l-0.246-2.031l-0.404,0.008C1382.619,866.678,1382.305,866.702,1381.988,866.739
                z"/>
            <path fill="#262425" d="M1385.895,886.661l-0.529,0.108c-0.285,0.059-0.586,0.106-0.889,0.145
                c-0.248,0.031-0.498,0.053-0.744,0.067c-5.861,0.327-11.01-3.984-11.725-9.815c-0.748-6.125,3.625-11.718,9.748-12.468
                c0.252-0.03,0.506-0.054,0.756-0.067l0.686-0.029l-0.338-2.752l-0.512,0.026c-0.313,0.018-0.623,0.045-0.924,0.081
                c-7.639,0.936-13.088,7.908-12.154,15.543c0.891,7.271,7.307,12.645,14.611,12.236c0,0.001,0.002,0,0.002,0
                c0.309-0.018,0.621-0.045,0.928-0.082c0.314-0.04,0.639-0.091,0.963-0.153l0.459-0.087L1385.895,886.661z"/>
        </g>
        <path fill="#262425" d="M1393.951,881.599c-0.291-0.224-0.711-0.336-1.258-0.336c-0.707,0-1.303,0.336-1.779,1.004
            c-0.477,0.67-0.715,1.582-0.715,2.737v5.295h-1.662v-10.385h1.662v2.141h0.041c0.236-0.73,0.598-1.301,1.086-1.71
            c0.484-0.407,1.029-0.614,1.631-0.614c0.434,0,0.764,0.05,0.994,0.144V881.599z"/>
        <path fill="#262425" d="M1399.578,890.542c-1.535,0-2.762-0.485-3.676-1.455c-0.918-0.971-1.375-2.255-1.375-3.858
            c0-1.744,0.477-3.105,1.43-4.086s2.24-1.47,3.863-1.47c1.549,0,2.754,0.476,3.625,1.429c0.867,0.954,1.303,2.275,1.303,3.965
            c0,1.656-0.469,2.982-1.404,3.98C1402.406,890.042,1401.152,890.542,1399.578,890.542z M1399.697,881.071
            c-1.066,0-1.912,0.363-2.533,1.091c-0.623,0.727-0.934,1.729-0.934,3.006c0,1.23,0.314,2.2,0.943,2.91
            c0.629,0.709,1.469,1.064,2.523,1.064c1.076,0,1.9-0.349,2.48-1.045c0.578-0.695,0.867-1.686,0.867-2.971
            c0-1.298-0.289-2.297-0.867-3C1401.598,881.423,1400.773,881.071,1399.697,881.071z"/>
        <path fill="#262425" d="M1419.965,879.915l-3.113,10.385h-1.723l-2.141-7.434c-0.08-0.283-0.135-0.604-0.16-0.964h-0.043
            c-0.02,0.244-0.09,0.559-0.211,0.943l-2.32,7.454h-1.664l-3.145-10.385h1.744l2.148,7.809c0.068,0.236,0.117,0.547,0.143,0.932
            h0.082c0.02-0.297,0.082-0.614,0.182-0.953l2.395-7.787h1.52l2.15,7.828c0.066,0.25,0.119,0.562,0.152,0.933h0.08
            c0.014-0.264,0.072-0.574,0.172-0.933l2.109-7.828H1419.965z"/>
        <path fill="#262425" d="M1430.234,890.299h-1.662v-1.766h-0.041c-0.773,1.338-1.959,2.008-3.568,2.008
            c-1.305,0-2.348-0.465-3.129-1.395c-0.781-0.929-1.172-2.194-1.172-3.797c0-1.718,0.434-3.093,1.297-4.128
            c0.867-1.033,2.02-1.55,3.459-1.55c1.426,0,2.463,0.561,3.113,1.683h0.041v-6.429h1.662V890.299z M1428.572,885.604v-1.531
            c0-0.839-0.279-1.548-0.832-2.129c-0.555-0.583-1.258-0.873-2.109-0.873c-1.014,0-1.811,0.372-2.393,1.116
            c-0.582,0.743-0.871,1.771-0.871,3.082c0,1.196,0.277,2.141,0.836,2.833c0.557,0.693,1.305,1.04,2.244,1.04
            c0.928,0,1.68-0.335,2.256-1.004C1428.281,887.469,1428.572,886.624,1428.572,885.604z"/>
        <path fill="#262425" d="M1432.453,889.923v-1.785c0.906,0.669,1.904,1.004,2.992,1.004c1.459,0,2.189-0.487,2.189-1.461
            c0-0.275-0.063-0.512-0.189-0.704c-0.125-0.192-0.293-0.363-0.506-0.512s-0.463-0.281-0.752-0.4
            c-0.285-0.118-0.596-0.242-0.926-0.37c-0.459-0.183-0.863-0.367-1.213-0.554c-0.348-0.185-0.639-0.396-0.871-0.628
            c-0.232-0.233-0.408-0.498-0.525-0.797c-0.121-0.296-0.18-0.645-0.18-1.043c0-0.486,0.111-0.918,0.334-1.293
            s0.521-0.69,0.895-0.943c0.369-0.254,0.795-0.443,1.271-0.573c0.477-0.127,0.969-0.191,1.475-0.191c0.9,0,1.703,0.155,2.414,0.466
            v1.683c-0.764-0.5-1.643-0.75-2.637-0.75c-0.311,0-0.592,0.035-0.842,0.106c-0.248,0.072-0.465,0.171-0.645,0.3
            c-0.178,0.128-0.316,0.282-0.416,0.461c-0.096,0.18-0.145,0.377-0.145,0.593c0,0.271,0.049,0.498,0.145,0.68
            c0.1,0.183,0.242,0.345,0.434,0.486c0.188,0.142,0.418,0.271,0.689,0.386c0.27,0.115,0.578,0.24,0.922,0.375
            c0.459,0.176,0.871,0.356,1.234,0.543c0.367,0.186,0.678,0.395,0.936,0.629c0.256,0.232,0.455,0.501,0.592,0.806
            c0.139,0.304,0.209,0.665,0.209,1.084c0,0.515-0.115,0.961-0.34,1.339c-0.227,0.38-0.529,0.692-0.906,0.943
            c-0.381,0.25-0.816,0.437-1.309,0.558c-0.494,0.121-1.01,0.183-1.553,0.183C1434.162,890.542,1433.236,890.334,1432.453,889.923z"
            />
        <path fill="#262425" d="M1445.713,890.542c-1.533,0-2.76-0.485-3.676-1.455c-0.914-0.971-1.373-2.255-1.373-3.858
            c0-1.744,0.477-3.105,1.43-4.086s2.24-1.47,3.863-1.47c1.547,0,2.756,0.476,3.623,1.429c0.871,0.954,1.303,2.275,1.303,3.965
            c0,1.656-0.467,2.982-1.402,3.98C1448.543,890.042,1447.289,890.542,1445.713,890.542z M1445.836,881.071
            c-1.07,0-1.914,0.363-2.535,1.091c-0.623,0.727-0.934,1.729-0.934,3.006c0,1.23,0.314,2.2,0.943,2.91
            c0.629,0.709,1.471,1.064,2.525,1.064c1.074,0,1.902-0.349,2.479-1.045c0.578-0.695,0.867-1.686,0.867-2.971
            c0-1.298-0.289-2.297-0.867-3C1447.738,881.423,1446.91,881.071,1445.836,881.071z"/>
        <path fill="#262425" d="M1461.416,890.299h-1.66v-1.645h-0.045c-0.686,1.258-1.754,1.887-3.199,1.887
            c-2.477,0-3.713-1.474-3.713-4.421v-6.206h1.652v5.942c0,2.189,0.838,3.285,2.516,3.285c0.811,0,1.477-0.299,2.002-0.896
            c0.523-0.599,0.787-1.381,0.787-2.349v-5.982h1.66V890.299z"/>
        <path fill="#262425" d="M1469.67,881.599c-0.293-0.224-0.713-0.336-1.26-0.336c-0.709,0-1.301,0.336-1.777,1.004
            c-0.479,0.67-0.715,1.582-0.715,2.737v5.295h-1.664v-10.385h1.664v2.141h0.039c0.236-0.73,0.6-1.301,1.084-1.71
            c0.488-0.407,1.033-0.614,1.633-0.614c0.434,0,0.766,0.05,0.996,0.144V881.599z"/>
        <path fill="#262425" d="M1478.033,889.821c-0.799,0.481-1.744,0.721-2.84,0.721c-1.48,0-2.676-0.481-3.586-1.446
            c-0.908-0.962-1.363-2.211-1.363-3.745c0-1.71,0.49-3.084,1.471-4.122s2.287-1.556,3.924-1.556c0.912,0,1.717,0.169,2.414,0.507
            v1.703c-0.771-0.541-1.596-0.812-2.475-0.812c-1.061,0-1.932,0.381-2.611,1.142c-0.68,0.76-1.02,1.759-1.02,2.994
            c0,1.218,0.32,2.178,0.959,2.881s1.494,1.055,2.57,1.055c0.906,0,1.758-0.301,2.557-0.901V889.821z"/>
        <path fill="#262425" d="M1480.879,877.278c-0.297,0-0.553-0.101-0.76-0.304c-0.211-0.202-0.316-0.459-0.316-0.77
            c0-0.313,0.105-0.57,0.316-0.776c0.207-0.207,0.463-0.31,0.76-0.31c0.305,0,0.563,0.103,0.777,0.31
            c0.211,0.206,0.316,0.464,0.316,0.776c0,0.298-0.105,0.55-0.316,0.76C1481.441,877.175,1481.184,877.278,1480.879,877.278z
             M1481.691,890.299h-1.664v-10.385h1.664V890.299z"/>
        <path fill="#262425" d="M1493.156,890.299h-1.662v-5.923c0-2.204-0.807-3.306-2.416-3.306c-0.83,0-1.518,0.313-2.063,0.938
            c-0.543,0.626-0.816,1.414-0.816,2.368v5.923h-1.662v-10.385h1.662v1.725h0.041c0.785-1.312,1.92-1.967,3.406-1.967
            c1.135,0,2.004,0.366,2.607,1.1c0.6,0.732,0.902,1.793,0.902,3.179V890.299z"/>
        <path fill="#262425" d="M1504.633,889.466c0,3.813-1.826,5.719-5.475,5.719c-1.285,0-2.408-0.243-3.367-0.729v-1.663
            c1.17,0.647,2.283,0.974,3.346,0.974c2.555,0,3.832-1.359,3.832-4.076v-1.136h-0.041c-0.791,1.324-1.98,1.987-3.568,1.987
            c-1.291,0-2.332-0.462-3.119-1.385c-0.787-0.922-1.18-2.161-1.18-3.715c0-1.765,0.424-3.167,1.271-4.209
            c0.848-1.04,2.01-1.561,3.482-1.561c1.402,0,2.438,0.561,3.113,1.683h0.041v-1.44h1.664V889.466z M1502.969,885.604v-1.531
            c0-0.824-0.277-1.53-0.836-2.119s-1.252-0.883-2.084-0.883c-1.027,0-1.832,0.374-2.414,1.121c-0.582,0.746-0.871,1.793-0.871,3.137
            c0,1.157,0.279,2.081,0.838,2.773c0.557,0.693,1.295,1.04,2.213,1.04c0.934,0,1.691-0.332,2.275-0.994
            C1502.676,887.486,1502.969,886.637,1502.969,885.604z"/>
        <g>
            <path fill="#262425" d="M1345.551,806.122c0-7.448,3.824-14.05,9.719-18.14c1.182-11.366,11.311-20.247,23.631-20.247
                c0.381,0,0.76,0.008,1.133,0.024c2.986-3.036,6.848-5.288,11.207-6.397c2.02-0.513,4.1-0.77,6.182-0.77
                c3.121,0,6.102,0.57,8.83,1.606c0.146-0.056,0.293-0.109,0.441-0.162c0.004-0.002,0.01-0.004,0.018-0.006
                c0.148-0.053,0.299-0.105,0.449-0.155l0,0c2.479-0.83,5.145-1.283,7.924-1.283c1.957,0,3.857,0.224,5.676,0.646
                c2.135-0.936,4.51-1.458,7.016-1.458c6.434,0,12.018,3.441,14.813,8.484c5.57,0.418,10.27,3.765,12.377,8.413
                c5.75,1.291,10.279,6.327,10.279,12.318c0,0.575-0.043,1.141-0.121,1.694c2.15,2.087,3.705,4.768,4.373,7.693
                c0.668,2.92,0.439,6.018-0.645,8.809c-0.596,1.541-1.445,2.98-2.496,4.254c-0.754,0.939-1.813,1.597-2.564,2.5
                c-1.764,1.38-3.832,2.422-6.094,3.025c-0.031,7.593-6.545,13.739-14.576,13.739c-0.076,0-0.156-0.003-0.234-0.004
                c-2.043,2.003-4.908,3.251-8.082,3.251c-3.674,0-6.939-1.674-8.988-4.256c-3.699,7.863-12.025,13.344-21.707,13.344
                c-3.973,0-7.717-0.923-11.01-2.556c-3.174,4.845-8.844,8.075-15.313,8.075c-5.891,0-11.121-2.68-14.406-6.82
                c-9.297-0.088-16.803-7.246-16.803-16.067c0-2.942,0.838-5.702,2.297-8.076C1346.764,814.242,1345.551,810.316,1345.551,806.122z"
                />
            <path fill="#00A0AF" d="M1412.836,831.196c-0.648-1.534-1.578-2.912-2.76-4.096c-1.184-1.183-2.563-2.11-4.096-2.76
                c-1.59-0.672-3.277-1.014-5.016-1.014h-33.139c-5.307,0-9.625-4.317-9.625-9.623v-4.024h-3.258v4.024
                c0,1.738,0.34,3.426,1.014,5.015c0.648,1.534,1.576,2.911,2.76,4.095s2.561,2.111,4.096,2.761
                c1.588,0.672,3.275,1.013,5.014,1.013h33.139c5.309,0,9.625,4.317,9.625,9.624v39.61h3.26v-39.61
                C1413.85,834.472,1413.508,832.786,1412.836,831.196z"/>
            <path fill="#95A43A" d="M1407.541,805.401c-0.648-1.534-1.578-2.911-2.76-4.094c-1.182-1.184-2.561-2.111-4.094-2.76
                c-1.59-0.673-3.277-1.014-5.016-1.014h-10.41v3.259h10.41c5.307,0,9.625,4.317,9.625,9.624v65.404h3.258v-65.404
                C1408.555,808.678,1408.213,806.991,1407.541,805.401z"/>
            <path fill="#F7941E" d="M1432.52,784.854v45.562c0,5.306-4.318,9.624-9.625,9.624h-9.965c-1.738,0-3.426,0.341-5.016,1.013
                c-1.533,0.648-2.91,1.578-4.094,2.761c-1.184,1.182-2.111,2.56-2.76,4.094c-0.674,1.59-1.014,3.277-1.014,5.016v22.898h3.26
                v-22.898c0-5.307,4.316-9.624,9.623-9.624h9.965c1.738,0,3.424-0.341,5.014-1.014c1.535-0.649,2.914-1.576,4.094-2.761
                c1.184-1.183,2.113-2.56,2.762-4.094c0.674-1.59,1.014-3.277,1.014-5.015v-45.562H1432.52z"/>
            <path fill="#FEBC11" d="M1374.729,802.239v23.878c0,5.306,4.316,9.622,9.623,9.622h0.857c1.74,0,3.426,0.342,5.016,1.013
                c1.533,0.649,2.91,1.579,4.094,2.761c1.182,1.184,2.111,2.562,2.76,4.096c0.672,1.589,1.014,3.275,1.014,5.015v27.198h-3.26
                v-27.198c0-5.308-4.316-9.625-9.623-9.625l-0.857,0.001c-1.738,0-3.426-0.341-5.014-1.013c-1.535-0.648-2.914-1.578-4.096-2.761
                c-1.182-1.182-2.111-2.561-2.76-4.094c-0.672-1.59-1.014-3.276-1.014-5.015v-23.878H1374.729z"/>
            <path fill="#F26522" d="M1450.529,803.284v10.021c0,5.307-4.318,9.624-9.625,9.624l-12.219-0.006c-1.74,0-3.426,0.34-5.016,1.013
                c-1.535,0.648-2.912,1.577-4.096,2.761c-1.182,1.182-2.111,2.561-2.76,4.094c-0.672,1.59-1.014,3.276-1.014,5.015v40.016h3.26
                v-40.016c0-5.306,4.316-9.623,9.623-9.623l1.588,0.006h10.631c1.74,0,3.428-0.341,5.016-1.014c1.535-0.648,2.914-1.577,4.096-2.76
                c1.184-1.184,2.111-2.561,2.76-4.095c0.674-1.59,1.014-3.276,1.014-5.015v-10.021H1450.529z"/>
            <path fill="#F7941E" d="M1413.006,784.274c3.416-2.114,8.217-3.434,13.535-3.434c10.357,0,18.752,4.995,18.752,11.158
                c0,5.888-7.664,10.71-17.383,11.126c-0.451,0.02-0.908,0.029-1.369,0.029c-1.17,0-2.313-0.063-3.422-0.185
                c-0.107,0.516-0.166,1.05-0.166,1.596c0,1.653,0.52,3.186,1.398,4.444c-3.047-0.884-5.32-3.584-5.57-6.852
                c-6.484-1.756-10.994-5.646-10.994-10.159C1407.787,789.001,1409.775,786.279,1413.006,784.274z"/>
            <g>
                <path fill="#00ADBA" d="M1406.861,778.497c-0.035-1.56-0.385-3.137-1.082-4.641c-0.043-0.091-0.084-0.181-0.131-0.271
                    c0.533-0.37,1.057-0.756,1.572-1.154c-0.457-0.867-1.01-1.687-1.645-2.437c-0.566,0.321-1.127,0.656-1.674,1.005
                    c-1.152-1.29-2.563-2.29-4.119-2.96c0.17-0.621,0.326-1.249,0.465-1.881c-0.918-0.368-1.871-0.637-2.848-0.802
                    c-0.229,0.607-0.443,1.218-0.639,1.833c-1.67-0.239-3.406-0.129-5.098,0.374c-0.273-0.581-0.561-1.156-0.861-1.726
                    c-0.947,0.307-1.855,0.711-2.717,1.207c0.217,0.607,0.447,1.208,0.693,1.802c-1.508,0.922-2.756,2.139-3.691,3.535
                    c-0.588-0.268-1.182-0.522-1.783-0.763c-0.531,0.836-0.975,1.726-1.314,2.651c0.561,0.324,1.129,0.633,1.703,0.927
                    c-0.547,1.605-0.748,3.326-0.564,5.042c-0.625,0.171-1.25,0.356-1.869,0.556c0.131,0.977,0.367,1.938,0.705,2.857
                    c0.643-0.111,1.281-0.24,1.916-0.383c0.082,0.211,0.172,0.42,0.27,0.628c0.641,1.382,1.52,2.577,2.563,3.563
                    c-0.373,0.53-0.734,1.067-1.084,1.617c0.732,0.657,1.537,1.235,2.395,1.721c0.424-0.495,0.832-0.999,1.23-1.514
                    c1.506,0.805,3.178,1.278,4.902,1.381c0.051,0.639,0.123,1.28,0.209,1.92c0.99,0.034,1.986-0.039,2.965-0.217
                    c0.006-0.646-0.006-1.291-0.031-1.934c0.887-0.186,1.768-0.474,2.627-0.872c0.736-0.342,1.42-0.748,2.051-1.21
                    c0.455,0.454,0.924,0.896,1.404,1.326c0.787-0.605,1.51-1.298,2.148-2.057c-0.412-0.493-0.84-0.978-1.275-1.45
                    c1.078-1.35,1.846-2.912,2.26-4.564c0.646,0.053,1.293,0.089,1.943,0.11c0.217-0.961,0.326-1.945,0.328-2.93
                    C1408.146,778.674,1407.504,778.578,1406.861,778.497z M1398.566,786.6c-4.354,2.021-9.523,0.131-11.543-4.224
                    c-2.025-4.354-0.133-9.523,4.223-11.545c4.354-2.021,9.521-0.131,11.545,4.223C1404.811,779.41,1402.922,784.579,1398.566,786.6z
                    "/>
                    <ellipse transform="matrix(-0.4209 -0.9071 0.9071 -0.4209 1275.5967 2372.0928)" fill="#00ADBA" cx="1395.001" cy="778.859" rx="3.757" ry="3.802"/>
            </g>
            <polygon fill="#FAA21B" points="1363.504,831.36 1362.303,830.16 1360.691,834.13 1359.078,838.101 1363.049,836.489
                1367.02,834.876 1365.82,833.675 1368.533,830.96 1366.221,828.646 		"/>
            <path fill="#008FAF" d="M1380.953,810.113c0,0-3.121,4.555-3.121,6.277c0,1.722,1.396,3.118,3.121,3.118
                c1.721,0,3.117-1.396,3.117-3.118C1384.07,814.667,1380.953,810.113,1380.953,810.113z"/>
            <path fill="#FEBC11" d="M1460.424,804.076c0.021-0.589-0.023-1.176-0.127-1.754c-0.385-0.003-0.771,0.006-1.154,0.023
                c-0.213-1.025-0.645-1.963-1.24-2.767c0.273-0.273,0.537-0.554,0.795-0.84c-0.365-0.464-0.773-0.889-1.227-1.266
                c-0.297,0.249-0.586,0.503-0.869,0.765c-0.793-0.627-1.723-1.084-2.729-1.325c0.033-0.382,0.057-0.768,0.068-1.153
                c-0.574-0.124-1.164-0.183-1.752-0.18c-0.068,0.379-0.127,0.762-0.176,1.143c-0.135,0.005-0.271,0.013-0.408,0.025
                c-0.91,0.08-1.762,0.329-2.531,0.713c-0.223-0.313-0.451-0.622-0.689-0.928c-0.521,0.277-1.01,0.609-1.459,0.989
                c0.193,0.336,0.393,0.664,0.602,0.988c-0.76,0.679-1.369,1.517-1.777,2.456c-0.371-0.099-0.746-0.188-1.125-0.269
                c-0.221,0.546-0.383,1.115-0.48,1.696c0.361,0.134,0.727,0.257,1.094,0.37c-0.076,0.532-0.094,1.081-0.045,1.639
                c0.043,0.479,0.133,0.942,0.264,1.386c-0.348,0.164-0.693,0.336-1.033,0.518c0.18,0.56,0.422,1.101,0.719,1.607
                c0.363-0.13,0.725-0.27,1.078-0.419c0.539,0.872,1.264,1.614,2.113,2.176c-0.164,0.348-0.318,0.702-0.461,1.061
                c0.498,0.313,1.031,0.571,1.584,0.771c0.193-0.334,0.379-0.673,0.557-1.015c0.887,0.294,1.848,0.414,2.836,0.327
                c0.063-0.005,0.119-0.012,0.18-0.019c0.1,0.371,0.211,0.741,0.328,1.108c0.584-0.079,1.158-0.225,1.711-0.429
                c-0.066-0.379-0.143-0.758-0.225-1.133c0.961-0.385,1.813-0.97,2.506-1.703c0.316,0.22,0.639,0.432,0.967,0.637
                c0.395-0.438,0.74-0.915,1.033-1.427c-0.297-0.249-0.596-0.491-0.902-0.724c0.475-0.882,0.768-1.872,0.83-2.916
                C1459.66,804.174,1460.043,804.129,1460.424,804.076z"/>
            <path fill="#F7941E" d="M1390.033,816.77c2.9,2.862,7.57,2.828,10.43-0.071c2.863-2.9,2.83-7.571-0.07-10.432
                c-2.9-2.861-7.57-2.83-10.434,0.07C1387.102,809.239,1387.133,813.909,1390.033,816.77z M1391.029,816l0.977-0.872l1.9-1.701
                c0.363,0.25,0.783,0.384,1.207,0.402l0.5,2.501l0.258,1.284C1394.158,817.798,1392.381,817.262,1391.029,816z M1399.578,815.824
                c-0.758,0.767-1.664,1.291-2.629,1.575l-0.258-1.284l-0.498-2.501c0.24-0.113,0.465-0.271,0.666-0.472
                c0.145-0.148,0.268-0.311,0.365-0.484l2.455,0.693l1.26,0.356C1400.646,814.476,1400.191,815.2,1399.578,815.824z
                 M1401.24,812.647l-1.262-0.355l-2.455-0.691c0.014-0.402-0.076-0.806-0.27-1.17l1.9-1.701l0.977-0.874
                C1401.174,809.255,1401.547,811.001,1401.24,812.647z M1399.395,807.037l-0.975,0.873l-1.9,1.701
                c-0.365-0.251-0.783-0.385-1.207-0.401l-0.502-2.502l-0.256-1.284C1396.268,805.24,1398.047,805.775,1399.395,807.037z
                 M1390.848,807.213c0.756-0.767,1.662-1.291,2.627-1.576l0.258,1.285l0.5,2.502c-0.242,0.113-0.467,0.27-0.668,0.471
                c-0.145,0.147-0.268,0.312-0.365,0.484l-2.455-0.693l-1.262-0.355C1389.777,808.56,1390.232,807.837,1390.848,807.213z
                 M1390.447,810.745l2.455,0.693c-0.016,0.401,0.074,0.806,0.27,1.169l-1.9,1.701l-0.977,0.874
                c-1.047-1.399-1.416-3.146-1.109-4.792L1390.447,810.745z"/>
            <g>
                <polygon fill="#F6891F" points="1425.027,814.561 1419.6,815.681 1420.701,817.392 1416.473,820.115 1418.596,823.412
                    1422.824,820.689 1423.926,822.398 1427.191,817.92 1430.455,813.442 			"/>
                    <rect x="1413.719" y="821.67" transform="matrix(-0.5416 -0.8407 0.8407 -0.5416 1490.3523 2458.759)" fill="#F6891F" width="3.758" height="2.68"/>
            </g>
            <g>
                <polygon fill="#AAD9B5" points="1377.113,802.617 1380.105,800.365 1382.359,803.358 1381.467,804.029 1380.359,802.556
                    1379.91,805.747 1378.805,805.589 1379.256,802.401 1377.783,803.508 			"/>
                <polygon fill="#AAD9B5" points="1381.16,799.046 1380.445,798.658 1381.566,796.595 1380.277,796.977 1380.049,796.197
                    1382.666,795.42 1383.439,798.04 1382.66,798.27 1382.279,796.982 			"/>
            </g>
            <polygon fill="#F26522" points="1449.199,776.851 1446.027,776.851 1446.027,773.679 1443.82,773.679 1443.82,776.851
                1440.648,776.851 1440.648,779.058 1443.82,779.058 1443.82,782.229 1446.027,782.229 1446.027,779.058 1449.199,779.058 		"/>
            <polygon fill="#F5EB00" points="1361.473,801.464 1358.096,801.464 1358.096,798.086 1355.744,798.086 1355.744,801.464
                1352.367,801.464 1352.367,803.813 1355.744,803.813 1355.744,807.192 1358.096,807.192 1358.096,803.813 1361.473,803.813 		"/>
            <polygon fill="#8DC63F" points="1433.248,770.262 1435.027,768.052 1428.143,766.085 1421.258,764.117 1424.646,770.425
                1428.035,776.733 1429.814,774.523 1434.814,778.548 1438.244,774.288 		"/>
            <polygon fill="#4EA0B4" points="1412.785,771.232 1414.703,772.526 1410.832,778.26 1411.479,778.697 1415.348,772.962
                1417.264,774.255 1417.453,771.56 1417.641,768.865 		"/>
            <path fill="#00A0AF" d="M1400.586,830.564c-1.459-1.461-3.826-1.461-5.287,0c-1.459,1.459-1.459,3.827,0,5.287
                c1.461,1.459,3.828,1.459,5.287,0C1402.047,834.391,1402.047,832.023,1400.586,830.564z M1398.928,834.193
                c-0.545,0.544-1.426,0.544-1.971,0c-0.543-0.545-0.543-1.427,0-1.972c0.545-0.544,1.428-0.544,1.971,0
                C1399.473,832.766,1399.473,833.648,1398.928,834.193z"/>
            <polygon fill="#79AD36" points="1370.357,842.967 1371.236,840.32 1379.15,842.942 1379.445,842.048 1371.531,839.428
                1372.408,836.782 1369.217,837.441 1366.025,838.101 		"/>
            <polygon fill="#17AEB2" points="1413.465,813.562 1414.346,813.513 1414.188,810.542 1417.268,810.467 1413.484,804.261
                1410.002,810.642 1413.305,810.563 		"/>
            <g>
                <path fill="#BFE2CE" d="M1375.795,787.703l-4.326-0.72l0.721-4.323c0.072-0.434-0.223-0.845-0.656-0.917
                    c-0.434-0.073-0.846,0.222-0.916,0.655l-0.721,4.323l-4.324-0.72c-0.434-0.072-0.844,0.222-0.916,0.655
                    c-0.072,0.435,0.221,0.845,0.654,0.918l4.324,0.719l-0.719,4.323c-0.072,0.436,0.221,0.846,0.654,0.917
                    c0.436,0.073,0.846-0.221,0.918-0.655l0.719-4.322l4.324,0.719c0.434,0.073,0.846-0.221,0.916-0.655
                    C1376.521,788.185,1376.227,787.775,1375.795,787.703z"/>
                <path fill="#95A43A" d="M1379.898,787.631c0.541-0.087,1.078-0.188,1.613-0.301c-0.023-0.865-0.148-1.727-0.375-2.561
                    c-0.549,0.041-1.094,0.099-1.635,0.17c-0.301-0.995-0.766-1.943-1.391-2.803c0.387-0.387,0.762-0.784,1.129-1.192
                    c-0.527-0.687-1.141-1.309-1.813-1.85c-0.42,0.357-0.826,0.724-1.223,1.101c-0.859-0.654-1.797-1.136-2.771-1.451
                    c0.086-0.54,0.156-1.083,0.213-1.629c-0.83-0.245-1.689-0.39-2.555-0.431c-0.129,0.533-0.242,1.07-0.34,1.607
                    c-1.051-0.021-2.098,0.134-3.096,0.458c-0.248-0.486-0.51-0.968-0.781-1.444c-0.816,0.29-1.6,0.676-2.324,1.151
                    c0.209,0.508,0.432,1.009,0.668,1.501c-0.771,0.536-1.475,1.198-2.076,1.982c-0.055,0.069-0.107,0.14-0.158,0.211
                    c-0.486-0.249-0.98-0.486-1.48-0.709c-0.488,0.713-0.895,1.485-1.203,2.295c0.467,0.288,0.941,0.563,1.422,0.822
                    c-0.348,1.002-0.52,2.048-0.518,3.085c-0.539,0.087-1.078,0.187-1.615,0.302c0.025,0.864,0.15,1.725,0.379,2.56
                    c0.547-0.04,1.094-0.099,1.635-0.169c0.297,0.994,0.764,1.944,1.391,2.803c-0.387,0.387-0.764,0.782-1.131,1.191
                    c0.529,0.688,1.141,1.309,1.813,1.85c0.418-0.356,0.826-0.723,1.223-1.1c0.861,0.653,1.797,1.135,2.771,1.45
                    c-0.084,0.54-0.156,1.083-0.213,1.63c0.83,0.243,1.689,0.389,2.555,0.43c0.129-0.533,0.242-1.069,0.342-1.608
                    c1.049,0.022,2.096-0.132,3.096-0.458c0.248,0.488,0.508,0.97,0.781,1.445c0.814-0.29,1.598-0.675,2.322-1.15
                    c-0.209-0.509-0.43-1.009-0.668-1.503c0.771-0.535,1.475-1.197,2.078-1.981c0.053-0.068,0.104-0.139,0.156-0.21
                    c0.486,0.249,0.98,0.485,1.482,0.71c0.488-0.714,0.895-1.486,1.201-2.297c-0.467-0.288-0.941-0.563-1.424-0.822
                    C1379.73,789.712,1379.898,788.669,1379.898,787.631z"/>
                <g>
                    <path fill="#A9BF38" d="M1369.6,793.358c3.16,0.527,6.146-1.608,6.674-4.768c0.525-3.159-1.611-6.147-4.77-6.672
                        c-3.158-0.527-6.145,1.608-6.672,4.769C1364.305,789.844,1366.441,792.833,1369.6,793.358z M1371.252,783.426
                        c2.328,0.387,3.898,2.587,3.514,4.913c-0.387,2.326-2.588,3.898-4.916,3.511c-2.326-0.387-3.896-2.587-3.51-4.913
                        C1366.727,784.613,1368.928,783.039,1371.252,783.426z"/>
                    <circle fill="#A9BF38" cx="1370.552" cy="787.639" r="2.161"/>
                </g>
            </g>
            <g>
                <path fill="#BFB131" d="M1455.709,781.437c-3.273-0.016-5.939,2.626-5.955,5.898c-0.016,3.274,2.627,5.938,5.898,5.954
                    c3.273,0.016,5.939-2.626,5.953-5.898C1461.621,784.118,1458.98,781.455,1455.709,781.437z M1455.66,791.728
                    c-2.412-0.012-4.355-1.975-4.344-4.384c0.012-2.412,1.975-4.356,4.383-4.344c2.412,0.01,4.355,1.973,4.346,4.383
                    C1460.033,789.793,1458.07,791.739,1455.66,791.728z"/>
                <circle fill="#CFB52B" cx="1455.68" cy="787.364" r="2.209"/>
            </g>
            <circle fill="#8DC63F" cx="1364.894" cy="815.17" r="3.026"/>
            <g>
                <path fill="#FEBC11" d="M1351.551,827.231c-0.484-0.338-0.771-0.767-0.85-1.282c-0.084-0.517,0.057-1.033,0.418-1.555
                    c0.398-0.569,0.871-0.909,1.422-1.022c0.547-0.113,1.086,0.015,1.613,0.382c0.504,0.352,0.795,0.778,0.869,1.279
                    c0.072,0.5-0.08,1.023-0.463,1.571c-0.391,0.562-0.859,0.897-1.404,1.009C1352.611,827.725,1352.076,827.596,1351.551,827.231z
                     M1358.959,827.248l-9.221,3.729l-1.162-0.812l9.211-3.735L1358.959,827.248z M1353.539,824.573
                    c-0.463-0.323-0.918-0.163-1.369,0.481c-0.426,0.61-0.418,1.069,0.023,1.377c0.451,0.315,0.898,0.155,1.342-0.481
                    C1353.967,825.331,1353.969,824.872,1353.539,824.573z M1353.391,833.664c-0.488-0.338-0.77-0.766-0.852-1.28
                    c-0.084-0.516,0.059-1.034,0.42-1.556c0.396-0.568,0.871-0.908,1.42-1.021s1.086,0.015,1.615,0.381
                    c0.508,0.354,0.799,0.78,0.875,1.276c0.074,0.496-0.08,1.017-0.461,1.564c-0.393,0.563-0.861,0.899-1.41,1.015
                    C1354.451,834.156,1353.914,834.031,1353.391,833.664z M1355.365,830.997c-0.461-0.321-0.916-0.159-1.367,0.485
                    c-0.424,0.61-0.414,1.071,0.035,1.385c0.453,0.314,0.9,0.153,1.342-0.481c0.207-0.295,0.305-0.566,0.297-0.813
                    C1355.666,831.326,1355.563,831.133,1355.365,830.997z"/>
            </g>
            <g>
                <path fill="#95A43A" d="M1444.211,817.115l0.65,0.986l-0.664,0.437l-0.631-0.959c-0.617,0.402-1.281,0.64-1.994,0.713
                    l-0.828-1.26c0.27,0.026,0.619-0.015,1.047-0.121c0.428-0.109,0.783-0.243,1.07-0.404l-1.09-1.654
                    c-0.883,0.203-1.553,0.235-2.012,0.099c-0.465-0.137-0.84-0.427-1.133-0.869c-0.311-0.476-0.41-0.991-0.295-1.548
                    c0.117-0.555,0.434-1.044,0.953-1.463l-0.557-0.845l0.664-0.437l0.541,0.827c0.662-0.39,1.207-0.602,1.637-0.633l0.809,1.228
                    c-0.594,0.021-1.174,0.177-1.742,0.469l1.133,1.722c0.82-0.196,1.475-0.236,1.965-0.118s0.879,0.396,1.166,0.832
                    c0.332,0.504,0.438,1.013,0.318,1.532C1445.102,816.167,1444.764,816.657,1444.211,817.115z M1440.934,813.576l-0.949-1.44
                    c-0.371,0.352-0.438,0.71-0.199,1.073C1439.992,813.52,1440.373,813.642,1440.934,813.576z M1442.607,814.674l0.904,1.377
                    c0.391-0.351,0.463-0.71,0.221-1.077C1443.539,814.677,1443.164,814.578,1442.607,814.674z"/>
            </g>
            <g>
                <path fill="#FEBC11" d="M1432.533,796.776l-1.453-0.609l-1.084,2.581l-2.436-1.021l1.08-2.581l-5.287-2.217l0.711-1.7
                    l8.385-5.719l2.633,1.104l-3.205,7.649l1.455,0.609L1432.533,796.776z M1431.559,788.28l-0.051-0.021
                    c-0.164,0.17-0.449,0.423-0.854,0.757l-4.285,2.939l3.07,1.286l1.598-3.816C1431.18,789.086,1431.354,788.705,1431.559,788.28z"
                    />
            </g>
        </g>
        <g>
            <path fill="#F7941E" d="M1386.84,902.455c0,3.108-1.617,3.467-2.834,3.467c-1.27,0-2.959-0.374-2.959-3.607v-8.71h-2.678v8.814
                c0,4.979,2.973,6.023,5.467,6.023c2.594,0,5.686-1.082,5.686-6.24v-8.598h-2.682V902.455z"/>
            <path fill="#FEBC11" d="M1395.715,906.026c-0.33,0-0.555-0.068-0.664-0.208c-0.08-0.099-0.211-0.365-0.211-1.043v-4.89h2.432
                v-2.418h-2.432v-3.06l-2.643,0.854v2.205h-1.658v2.418h1.658v5.163c0,2.176,1.096,3.375,3.084,3.375
                c0.689,0,1.264-0.124,1.707-0.369l0.283-0.157v-2.745l-0.879,0.665C1396.205,905.958,1395.984,906.026,1395.715,906.026z"/>
            <path fill="#95A43A" d="M1403.451,897.242c-1.656,0-2.998,0.517-3.986,1.532c-0.982,1.014-1.484,2.421-1.484,4.182
                c0,1.627,0.479,2.96,1.428,3.963c0.953,1.011,2.238,1.523,3.814,1.523c1.615,0,2.93-0.525,3.9-1.563
                c0.965-1.027,1.457-2.397,1.457-4.074c0-1.704-0.457-3.068-1.355-4.055C1406.313,897.75,1405.043,897.242,1403.451,897.242z
                 M1405.217,905.273c-0.43,0.52-1.045,0.772-1.881,0.772c-0.824,0-1.455-0.265-1.936-0.805c-0.49-0.553-0.738-1.341-0.738-2.342
                c0-1.047,0.248-1.868,0.734-2.438c0.473-0.552,1.107-0.82,1.939-0.82c0.834,0,1.449,0.256,1.881,0.78
                c0.453,0.55,0.682,1.37,0.682,2.441C1405.898,903.918,1405.67,904.73,1405.217,905.273z"/>
            <path fill="#00A0AF" d="M1415.525,897.242c-1.119,0-2.064,0.333-2.824,0.993v-0.768h-2.643v15.188h2.643v-4.987
                c0.658,0.514,1.455,0.773,2.381,0.773c1.531,0,2.76-0.558,3.65-1.659c0.863-1.066,1.301-2.487,1.301-4.225
                c0-1.572-0.393-2.855-1.17-3.815C1418.061,897.748,1416.938,897.242,1415.525,897.242z M1413.385,900.39
                c0.441-0.505,1.016-0.75,1.754-0.75c0.682,0,1.203,0.233,1.592,0.712c0.412,0.506,0.621,1.239,0.621,2.18
                c0,1.149-0.23,2.045-0.688,2.66c-0.426,0.574-1,0.854-1.76,0.854c-0.645,0-1.16-0.217-1.574-0.66
                c-0.424-0.454-0.629-0.997-0.629-1.658v-1.346C1412.701,901.567,1412.926,900.917,1413.385,900.39z"/>
            <rect x="1421.512" y="897.467" fill="#95A43A" width="2.643" height="10.749"/>
            <path fill="#F26522" d="M1430.35,897.242c-1.34,0-2.541,0.333-3.574,0.991l-0.256,0.161v3.067l0.904-0.764
                c0.842-0.713,1.754-1.058,2.795-1.058c0.635,0,1.289,0.183,1.416,1.438l-2.396,0.335c-3.025,0.421-3.66,2.24-3.66,3.691
                c0,0.997,0.332,1.813,0.988,2.427c0.645,0.604,1.531,0.911,2.633,0.911c0.967,0,1.787-0.276,2.457-0.823v0.598h2.645v-6.825
                C1434.301,898.677,1432.934,897.242,1430.35,897.242z M1429.557,906.045c-0.424,0-0.742-0.102-0.979-0.311
                c-0.219-0.192-0.32-0.43-0.32-0.743c0-0.454,0.105-0.759,0.314-0.903c0.287-0.203,0.756-0.352,1.379-0.438l1.705-0.235v0.333
                c0,0.686-0.197,1.23-0.602,1.666C1430.66,905.838,1430.17,906.045,1429.557,906.045z"/>
            <path fill="#95A43A" d="M1422.799,895.921c0,0,1.604-2.345,1.604-3.231s-0.719-1.606-1.604-1.606c-0.889,0-1.609,0.72-1.609,1.606
                S1422.799,895.921,1422.799,895.921z"/>
        </g>
    </g>
    <g>
        <path fill="#00A0AF" d="M836.888,599.931c-10.137,1.24-17.377,10.5-16.138,20.639c1.184,9.65,9.705,16.787,19.405,16.244l0,0
            c0.414-0.018,0.827-0.057,1.234-0.109c0.63-0.075,1.269-0.186,1.892-0.326l0.805-0.187l-0.508-4.14l-0.994,0.245
            c-0.565,0.13-1.136,0.245-1.695,0.309c-7.918,0.965-15.08-4.724-16.039-12.538c-0.962-7.879,4.663-15.071,12.536-16.041
            c0.579-0.067,1.154-0.102,1.723-0.102l1.026-0.007l-0.505-4.139l-0.825,0.013C838.173,599.807,837.535,599.853,836.888,599.931z"/>
        <path fill="#00A0AF" d="M844.884,640.861l-1.098,0.23c-0.593,0.121-1.21,0.224-1.835,0.302c-0.512,0.061-1.028,0.109-1.54,0.138
            c-12.111,0.673-22.749-8.242-24.227-20.291c-1.548-12.657,7.493-24.212,20.146-25.765c0.518-0.058,1.042-0.104,1.562-0.136
            l1.412-0.064l-0.697-5.685l-1.058,0.055c-0.644,0.034-1.285,0.086-1.91,0.169c-15.78,1.927-27.046,16.337-25.116,32.118
            c1.836,15.027,15.102,26.131,30.2,25.29c0,0.004,0,0,0,0c0.635-0.036,1.283-0.093,1.917-0.169c0.65-0.079,1.32-0.188,1.994-0.316
            l0.943-0.178L844.884,640.861z"/>
    </g>
    <path fill="#262425" d="M864.161,625.572c-0.708-0.546-1.73-0.815-3.059-0.815c-1.729,0-3.177,0.815-4.336,2.446
        c-1.161,1.624-1.738,3.849-1.738,6.664v12.885h-4.053v-25.275h4.053v5.207h0.1c0.576-1.775,1.455-3.164,2.64-4.158
        c1.184-0.994,2.51-1.49,3.975-1.49c1.049,0,1.858,0.109,2.418,0.342V625.572z"/>
    <path fill="#262425" d="M877.852,647.341c-3.733,0-6.715-1.177-8.947-3.544c-2.228-2.355-3.343-5.484-3.343-9.389
        c0-4.246,1.161-7.563,3.481-9.943c2.319-2.392,5.455-3.581,9.403-3.581c3.769,0,6.709,1.158,8.824,3.478
        c2.118,2.321,3.176,5.538,3.176,9.654c0,4.028-1.145,7.261-3.421,9.688C884.744,646.129,881.689,647.341,877.852,647.341z
         M878.148,624.289c-2.6,0-4.656,0.884-6.17,2.655c-1.515,1.769-2.268,4.203-2.268,7.317c0,2.999,0.763,5.359,2.295,7.082
        c1.525,1.733,3.579,2.59,6.143,2.59c2.617,0,4.627-0.84,6.038-2.54c1.405-1.698,2.107-4.107,2.107-7.231
        c0-3.157-0.703-5.592-2.107-7.308C882.776,625.146,880.766,624.289,878.148,624.289z"/>
    <path fill="#262425" d="M927.484,621.476l-7.575,25.275h-4.195l-5.209-18.094c-0.198-0.69-0.328-1.469-0.395-2.349h-0.097
        c-0.052,0.592-0.225,1.363-0.52,2.301l-5.653,18.141h-4.046l-7.653-25.275h4.247l5.233,19.009c0.162,0.57,0.277,1.324,0.343,2.271
        h0.201c0.043-0.728,0.196-1.502,0.44-2.321l5.826-18.959h3.706l5.229,19.054c0.164,0.609,0.29,1.362,0.37,2.275h0.2
        c0.032-0.647,0.171-1.4,0.421-2.275l5.13-19.054H927.484z"/>
    <path fill="#262425" d="M952.491,646.751h-4.053v-4.298h-0.099c-1.877,3.264-4.772,4.888-8.688,4.888
        c-3.179,0-5.712-1.126-7.614-3.393c-1.902-2.263-2.851-5.347-2.851-9.244c0-4.179,1.053-7.531,3.159-10.042
        c2.102-2.52,4.911-3.779,8.419-3.779c3.471,0,5.995,1.366,7.575,4.096h0.099v-15.647h4.053V646.751z M948.438,635.327v-3.731
        c0-2.042-0.673-3.767-2.024-5.182c-1.347-1.412-3.061-2.125-5.134-2.125c-2.468,0-4.41,0.903-5.825,2.718
        c-1.414,1.811-2.124,4.307-2.124,7.503c0,2.909,0.68,5.205,2.038,6.897c1.356,1.685,3.179,2.524,5.467,2.524
        c2.255,0,4.084-0.811,5.492-2.437C947.735,639.863,948.438,637.812,948.438,635.327z"/>
    <path fill="#95A43A" d="M957.888,645.837v-4.341c2.2,1.625,4.631,2.437,7.278,2.437c3.557,0,5.335-1.177,5.335-3.549
        c0-0.676-0.154-1.247-0.456-1.715c-0.308-0.47-0.72-0.883-1.235-1.249c-0.519-0.359-1.132-0.689-1.826-0.971
        c-0.703-0.291-1.453-0.59-2.26-0.904c-1.118-0.445-2.101-0.891-2.949-1.346c-0.849-0.457-1.556-0.96-2.124-1.529
        c-0.567-0.565-0.994-1.22-1.281-1.939c-0.289-0.728-0.434-1.569-0.434-2.544c0-1.18,0.272-2.23,0.815-3.149
        c0.541-0.909,1.269-1.679,2.169-2.295c0.906-0.615,1.943-1.079,3.099-1.39c1.161-0.314,2.36-0.469,3.595-0.469
        c2.186,0,4.144,0.376,5.873,1.131v4.099c-1.858-1.219-4-1.825-6.418-1.825c-0.756,0-1.439,0.093-2.047,0.261
        c-0.61,0.166-1.134,0.416-1.567,0.728c-0.438,0.306-0.774,0.687-1.015,1.119c-0.235,0.44-0.354,0.92-0.354,1.448
        c0,0.661,0.119,1.206,0.354,1.646c0.24,0.449,0.588,0.843,1.051,1.19c0.46,0.348,1.019,0.656,1.676,0.934
        c0.659,0.286,1.408,0.587,2.249,0.923c1.118,0.418,2.122,0.868,3.01,1.315c0.89,0.451,1.642,0.964,2.271,1.53
        c0.628,0.566,1.107,1.223,1.448,1.965c0.335,0.739,0.504,1.621,0.504,2.639c0,1.252-0.275,2.337-0.829,3.261
        c-0.553,0.921-1.289,1.687-2.211,2.298c-0.921,0.605-1.98,1.06-3.182,1.357c-1.201,0.294-2.461,0.439-3.777,0.439
        C962.053,647.341,959.793,646.84,957.888,645.837z"/>
    <path fill="#262425" d="M990.168,647.341c-3.736,0-6.717-1.177-8.948-3.544c-2.231-2.355-3.346-5.484-3.346-9.389
        c0-4.246,1.163-7.563,3.479-9.943c2.32-2.392,5.456-3.581,9.405-3.581c3.77,0,6.707,1.158,8.822,3.478
        c2.116,2.321,3.173,5.538,3.173,9.654c0,4.028-1.136,7.261-3.416,9.688C997.058,646.129,994.001,647.341,990.168,647.341z
         M990.463,624.289c-2.601,0-4.655,0.884-6.168,2.655c-1.518,1.769-2.275,4.203-2.275,7.317c0,2.999,0.767,5.359,2.297,7.082
        c1.53,1.733,3.582,2.59,6.146,2.59c2.615,0,4.627-0.84,6.034-2.54c1.409-1.698,2.112-4.107,2.112-7.231
        c0-3.157-0.703-5.592-2.112-7.308C995.09,625.146,993.079,624.289,990.463,624.289z"/>
    <path fill="#262425" d="M1028.397,646.751h-4.047v-3.996h-0.102c-1.677,3.057-4.278,4.585-7.799,4.585
        c-6.024,0-9.035-3.584-9.035-10.762v-15.103h4.023v14.466c0,5.332,2.043,7.99,6.126,7.99c1.974,0,3.595-0.725,4.875-2.175
        c1.272-1.457,1.911-3.367,1.911-5.719v-14.563h4.047V646.751z"/>
    <path fill="#262425" d="M1048.483,625.572c-0.705-0.546-1.728-0.815-3.058-0.815c-1.729,0-3.176,0.815-4.332,2.446
        c-1.163,1.624-1.742,3.849-1.742,6.664v12.885h-4.046v-25.275h4.046v5.207h0.101c0.576-1.775,1.455-3.164,2.641-4.158
        c1.183-0.994,2.508-1.49,3.975-1.49c1.053,0,1.858,0.109,2.416,0.342V625.572z"/>
    <path fill="#262425" d="M1068.842,645.595c-1.943,1.165-4.246,1.747-6.909,1.747c-3.604,0-6.513-1.172-8.729-3.518
        c-2.215-2.343-3.318-5.382-3.318-9.118c0-4.162,1.192-7.509,3.58-10.034c2.384-2.525,5.566-3.787,9.551-3.787
        c2.223,0,4.181,0.407,5.876,1.234v4.146c-1.878-1.319-3.883-1.976-6.022-1.976c-2.585,0-4.706,0.927-6.358,2.771
        c-1.655,1.856-2.479,4.286-2.479,7.3c0,2.963,0.779,5.3,2.328,7.006c1.559,1.714,3.645,2.566,6.26,2.566
        c2.205,0,4.277-0.725,6.221-2.195V645.595z"/>
    <path fill="#262425" d="M1075.77,615.057c-0.721,0-1.338-0.245-1.846-0.739c-0.513-0.491-0.766-1.118-0.766-1.878
        c0-0.755,0.253-1.387,0.766-1.882c0.508-0.505,1.125-0.757,1.846-0.757c0.746,0,1.373,0.251,1.894,0.757
        c0.52,0.495,0.776,1.127,0.776,1.882c0,0.728-0.257,1.341-0.776,1.854C1077.143,614.806,1076.516,615.057,1075.77,615.057z
         M1077.748,646.751h-4.049v-25.275h4.049V646.751z"/>
    <path fill="#262425" d="M1105.66,646.751h-4.05v-14.416c0-5.363-1.956-8.046-5.874-8.046c-2.021,0-3.697,0.768-5.022,2.284
        c-1.324,1.523-1.985,3.445-1.985,5.762v14.416h-4.048v-25.275h4.048v4.196h0.099c1.909-3.192,4.671-4.788,8.295-4.788
        c2.764,0,4.878,0.89,6.341,2.679c1.463,1.783,2.197,4.364,2.197,7.732V646.751z"/>
    <path fill="#262425" d="M1133.594,644.722c0,9.285-4.443,13.93-13.324,13.93c-3.129,0-5.86-0.595-8.198-1.78v-4.053
        c2.848,1.587,5.564,2.372,8.146,2.372c6.222,0,9.334-3.305,9.334-9.922v-2.763h-0.1c-1.926,3.221-4.826,4.835-8.691,4.835
        c-3.143,0-5.67-1.122-7.589-3.368c-1.917-2.247-2.875-5.265-2.875-9.046c0-4.293,1.033-7.71,3.1-10.237
        c2.065-2.539,4.888-3.806,8.477-3.806c3.406,0,5.931,1.366,7.579,4.096h0.1v-3.504h4.043V644.722z M1129.551,635.327v-3.731
        c0-2.008-0.68-3.73-2.039-5.158c-1.357-1.431-3.049-2.148-5.073-2.148c-2.503,0-4.457,0.903-5.871,2.724
        c-1.419,1.818-2.126,4.369-2.126,7.645c0,2.813,0.68,5.058,2.039,6.75c1.354,1.685,3.155,2.524,5.392,2.524
        c2.27,0,4.116-0.806,5.544-2.418C1128.838,639.91,1129.551,637.844,1129.551,635.327z"/>
    <g>
        <path fill="#F26522" d="M889.701,676.221c0,5.86-2.477,8.711-7.57,8.711c-5.295,0-7.869-2.956-7.869-9.036v-19.651h-5.193v19.888
            c0,8.887,4.258,13.393,12.651,13.393c8.743,0,13.176-4.673,13.176-13.895v-19.386h-5.195V676.221z"/>
        <path fill="#95A43A" d="M906.41,658.353l-5.101,1.579v5.31h-4.04v4.355h4.04v12.661c0,5.969,3.723,7.218,6.845,7.218
            c1.559,0,2.851-0.257,3.834-0.777l0.344-0.178v-4.738l-1.072,0.774c-0.575,0.421-1.245,0.62-2.051,0.62
            c-1.02,0-1.739-0.244-2.138-0.722c-0.434-0.523-0.662-1.503-0.662-2.834v-12.024h5.923v-4.355h-5.923V658.353z"/>
        <path fill="#00A0AF" d="M925.888,664.715c-3.839,0-6.945,1.136-9.223,3.373c-2.272,2.238-3.424,5.365-3.424,9.295
            c0,3.625,1.107,6.583,3.293,8.794c2.195,2.218,5.16,3.348,8.805,3.348c3.742,0,6.773-1.16,9.014-3.438
            c2.229-2.271,3.359-5.316,3.359-9.055c0-3.809-1.049-6.835-3.123-9.015C932.498,665.827,929.574,664.715,925.888,664.715z
             M925.615,685.219c-2.208,0-3.913-0.68-5.217-2.087c-1.3-1.415-1.964-3.393-1.964-5.88c0-2.599,0.653-4.645,1.952-6.092
            c1.284-1.441,2.995-2.135,5.229-2.135c2.243,0,3.909,0.668,5.095,2.044c1.202,1.405,1.814,3.452,1.814,6.098
            c0,2.612-0.612,4.635-1.812,6.022C929.523,684.556,927.858,685.219,925.615,685.219z"/>
        <path fill="#F26522" d="M953.862,664.715c-3.117,0-5.652,1.088-7.549,3.238v-2.712h-5.098v34.1h5.098v-12.45
            c1.674,1.753,3.843,2.634,6.475,2.634c3.515,0,6.327-1.221,8.369-3.623c2.003-2.369,3.012-5.547,3.012-9.436
            c0-3.521-0.902-6.375-2.693-8.49C959.651,665.812,957.089,664.715,953.862,664.715z M958.98,676.397c0,2.815-0.608,5.03-1.81,6.575
            c-1.172,1.511-2.744,2.247-4.819,2.247c-1.771,0-3.179-0.573-4.323-1.74c-1.151-1.185-1.715-2.593-1.715-4.294v-3.135
            c0-2.062,0.6-3.716,1.832-5.057c1.212-1.329,2.774-1.968,4.779-1.968c1.869,0,3.308,0.615,4.393,1.889
            C958.42,672.213,958.98,674.058,958.98,676.397z"/>
        <rect x="967.672" y="665.241" fill="#00A0AF" width="5.102" height="23.759"/>
        <path fill="#00A0AF" d="M970.268,654.855c-0.85,0-1.588,0.291-2.188,0.859c-0.603,0.566-0.912,1.286-0.912,2.13
            c0,0.855,0.31,1.566,0.916,2.128c0.602,0.556,1.337,0.841,2.185,0.841c0.87,0,1.617-0.294,2.229-0.867
            c0.609-0.57,0.921-1.281,0.921-2.102c0-0.844-0.315-1.569-0.928-2.135C971.883,655.146,971.134,654.855,970.268,654.855z"/>
        <path fill="#95A43A" d="M987.088,664.715c-3.128,0-5.934,0.746-8.343,2.206l-0.313,0.189v5.418l1.099-0.894
            c2.146-1.729,4.583-2.608,7.238-2.608c1.714,0,3.973,0.479,4.164,4.537l-6.462,0.863c-5.528,0.73-8.33,3.409-8.33,7.959
            c0,2.143,0.736,3.883,2.195,5.194c1.444,1.285,3.445,1.946,5.949,1.946c2.744,0,4.976-0.942,6.656-2.801V689h5.102v-15.258
            C996.043,667.838,992.948,664.715,987.088,664.715z M990.942,677.721v1.51c0,1.762-0.54,3.177-1.646,4.313
            c-1.093,1.131-2.446,1.676-4.137,1.676c-1.201,0-2.127-0.288-2.823-0.88c-0.68-0.58-1.006-1.297-1.006-2.214
            c0-1.295,0.343-2.158,1.046-2.624c0.796-0.53,2.026-0.915,3.655-1.131L990.942,677.721z"/>
    </g>
    <g>
        <path fill="#262425" d="M689.444,632.896c0-6.9,3.545-13.018,9.005-16.807c1.097-10.533,10.482-18.761,21.897-18.761
            c0.353,0,0.701,0.009,1.05,0.024c2.766-2.813,6.344-4.9,10.381-5.927c1.871-0.477,3.8-0.714,5.728-0.714
            c2.893,0,5.654,0.529,8.183,1.489c0.136-0.052,0.271-0.102,0.407-0.15c0.005-0.003,0.011-0.005,0.018-0.006
            c0.137-0.049,0.275-0.097,0.414-0.145l0,0c2.297-0.769,4.77-1.188,7.345-1.188c1.812,0,3.572,0.208,5.257,0.6
            c1.977-0.867,4.179-1.352,6.499-1.352c5.963,0,11.136,3.19,13.726,7.862c5.162,0.386,9.513,3.487,11.466,7.794
            c5.33,1.196,9.524,5.863,9.524,11.413c0,0.533-0.038,1.057-0.11,1.569c1.992,1.933,3.431,4.417,4.05,7.131
            c0.619,2.703,0.409,5.574-0.595,8.16c-0.554,1.426-1.342,2.759-2.314,3.941c-0.698,0.87-1.679,1.479-2.376,2.314
            c-1.635,1.278-3.551,2.244-5.646,2.803c-0.03,7.037-6.064,12.731-13.504,12.731c-0.073,0-0.145,0-0.217-0.003
            c-1.893,1.855-4.549,3.01-7.488,3.01c-3.406,0-6.429-1.55-8.328-3.942c-3.428,7.285-11.143,12.363-20.112,12.363
            c-3.682,0-7.152-0.854-10.2-2.367c-2.941,4.491-8.195,7.48-14.188,7.48c-5.458,0-10.306-2.479-13.348-6.318
            c-8.613-0.08-15.57-6.714-15.57-14.885c0-2.728,0.775-5.286,2.13-7.484C690.568,640.42,689.444,636.782,689.444,632.896z"/>
        <path fill="#00A0AF" d="M751.788,656.129c-0.603-1.424-1.464-2.698-2.558-3.794c-1.097-1.096-2.373-1.956-3.794-2.559
            c-1.473-0.623-3.038-0.939-4.646-0.939h-30.704c-4.917,0-8.918-3.999-8.918-8.917v-3.728h-3.019v3.728
            c0,1.61,0.314,3.174,0.939,4.647c0.601,1.422,1.461,2.698,2.557,3.797c1.095,1.094,2.372,1.953,3.792,2.556
            c1.473,0.62,3.036,0.939,4.648,0.939h30.704c4.917,0,8.917,4,8.917,8.914v39.533h3.02v-39.533
            C752.727,659.164,752.411,657.601,751.788,656.129z"/>
        <path fill="#95A43A" d="M746.882,632.228c-0.602-1.419-1.462-2.697-2.557-3.79c-1.097-1.099-2.374-1.957-3.793-2.561
            c-1.475-0.622-3.037-0.938-4.648-0.938h-9.645v3.021h9.645c4.917,0,8.917,3.998,8.917,8.916v63.432h3.021v-63.432
            C747.822,635.265,747.504,633.7,746.882,632.228z"/>
        <path fill="#F7941E" d="M770.023,613.191v42.214c0,4.918-4,8.917-8.917,8.917h-9.23c-1.611,0-3.175,0.316-4.648,0.937
            c-1.42,0.603-2.697,1.464-3.793,2.56c-1.097,1.097-1.956,2.371-2.557,3.795c-0.624,1.471-0.938,3.034-0.938,4.647v24.046h3.019
            v-24.046c0-4.919,4-8.919,8.918-8.919h9.23c1.611,0,3.175-0.316,4.646-0.939c1.422-0.603,2.698-1.462,3.794-2.556
            c1.096-1.098,1.956-2.373,2.557-3.793c0.624-1.475,0.939-3.039,0.939-4.648v-42.214H770.023z"/>
        <path fill="#FEBC11" d="M716.479,629.3v22.122c0,4.917,4.001,8.916,8.917,8.916h0.793c1.611,0,3.174,0.317,4.646,0.94
            c1.422,0.6,2.697,1.46,3.794,2.557c1.096,1.096,1.957,2.372,2.558,3.793c0.623,1.475,0.938,3.035,0.938,4.647v28.031h-3.019
            v-28.031c0-4.917-4.001-8.92-8.917-8.92l-0.793,0.004c-1.61,0-3.174-0.315-4.647-0.939c-1.42-0.602-2.697-1.463-3.792-2.557
            c-1.096-1.097-1.958-2.373-2.558-3.794c-0.623-1.475-0.939-3.037-0.939-4.647V629.3H716.479z"/>
        <path fill="#F26522" d="M786.71,630.266v9.286c0,4.918-4,8.918-8.917,8.918l-11.321-0.007c-1.612,0-3.175,0.316-4.647,0.939
            c-1.421,0.602-2.698,1.46-3.794,2.558c-1.095,1.096-1.956,2.371-2.556,3.794c-0.625,1.474-0.939,3.037-0.939,4.646v39.907h3.02
            v-39.907c0-4.915,3.999-8.915,8.915-8.915l1.473,0.006h9.851c1.609,0,3.174-0.317,4.646-0.94c1.422-0.602,2.698-1.462,3.794-2.556
            c1.096-1.1,1.957-2.374,2.557-3.796c0.623-1.474,0.938-3.036,0.938-4.646v-9.286H786.71z"/>
        <path fill="#F7941E" d="M751.948,612.654c3.161-1.96,7.61-3.181,12.539-3.181c9.596,0,17.373,4.629,17.373,10.337
            c0,5.454-7.101,9.922-16.104,10.309c-0.42,0.02-0.842,0.027-1.269,0.027c-1.084,0-2.144-0.059-3.172-0.171
            c-0.1,0.479-0.151,0.973-0.151,1.479c0,1.531,0.479,2.95,1.294,4.116c-2.824-0.816-4.929-3.321-5.161-6.35
            c-6.009-1.624-10.188-5.229-10.188-9.411C747.109,617.034,748.951,614.512,751.948,612.654z"/>
        <g>
            <path fill="#00ADBA" d="M746.252,607.302c-0.033-1.445-0.357-2.906-1.003-4.3c-0.04-0.086-0.08-0.169-0.12-0.251
                c0.492-0.344,0.977-0.702,1.456-1.07c-0.423-0.806-0.936-1.564-1.524-2.258c-0.526,0.298-1.042,0.608-1.549,0.932
                c-1.068-1.195-2.375-2.122-3.817-2.743c0.158-0.574,0.3-1.156,0.431-1.743c-0.849-0.338-1.734-0.591-2.64-0.744
                c-0.212,0.565-0.411,1.129-0.591,1.7c-1.547-0.222-3.156-0.12-4.724,0.346c-0.25-0.539-0.519-1.071-0.799-1.597
                c-0.875,0.281-1.717,0.658-2.514,1.117c0.199,0.563,0.413,1.118,0.641,1.67c-1.398,0.854-2.552,1.98-3.421,3.273
                c-0.543-0.247-1.094-0.482-1.651-0.705c-0.493,0.774-0.903,1.599-1.217,2.458c0.52,0.298,1.045,0.583,1.577,0.857
                c-0.505,1.488-0.693,3.082-0.522,4.671c-0.58,0.158-1.157,0.332-1.732,0.517c0.121,0.903,0.342,1.792,0.654,2.647
                c0.594-0.105,1.187-0.225,1.772-0.357c0.078,0.195,0.162,0.391,0.252,0.582c0.593,1.279,1.406,2.389,2.374,3.302
                c-0.345,0.492-0.681,0.99-1.004,1.498c0.679,0.61,1.425,1.147,2.219,1.597c0.392-0.459,0.771-0.928,1.137-1.403
                c1.396,0.744,2.948,1.183,4.544,1.279c0.048,0.592,0.114,1.185,0.193,1.777c0.918,0.031,1.842-0.034,2.748-0.2
                c0.004-0.599-0.004-1.198-0.03-1.791c0.823-0.172,1.64-0.438,2.435-0.808c0.682-0.317,1.316-0.693,1.9-1.12
                c0.424,0.418,0.856,0.828,1.302,1.228c0.73-0.56,1.398-1.204,1.989-1.906c-0.382-0.457-0.777-0.905-1.182-1.343
                c1-1.252,1.712-2.698,2.096-4.23c0.597,0.047,1.198,0.084,1.8,0.104c0.2-0.89,0.3-1.803,0.304-2.716
                C747.442,607.467,746.848,607.376,746.252,607.302z M738.567,614.809c-4.036,1.874-8.823,0.12-10.697-3.914
                c-1.872-4.033-0.12-8.821,3.915-10.695c4.034-1.873,8.822-0.121,10.696,3.913C744.354,608.148,742.602,612.934,738.567,614.809z"
                />
                <ellipse transform="matrix(-0.4212 -0.907 0.907 -0.4212 493.8086 1530.4246)" fill="#00ADBA" cx="735.263" cy="607.638" rx="3.481" ry="3.523"/>
        </g>
        <polygon fill="#FAA21B" points="706.08,656.279 704.968,655.168 703.474,658.848 701.98,662.524 705.66,661.034 709.337,659.539
            708.225,658.425 710.74,655.909 708.596,653.766 	"/>
        <path fill="#008FAF" d="M722.246,636.594c0,0-2.888,4.22-2.888,5.815c0,1.597,1.293,2.89,2.888,2.89
            c1.597,0,2.891-1.293,2.891-2.89C725.137,640.813,722.246,636.594,722.246,636.594z"/>
        <path fill="#FEBC11" d="M795.88,631.001c0.019-0.546-0.021-1.09-0.118-1.626c-0.357-0.001-0.714,0.006-1.071,0.023
            c-0.195-0.952-0.596-1.821-1.147-2.563c0.25-0.254,0.496-0.514,0.736-0.779c-0.337-0.43-0.718-0.823-1.137-1.171
            c-0.274,0.229-0.543,0.464-0.805,0.706c-0.735-0.58-1.596-1.004-2.528-1.227c0.032-0.355,0.051-0.713,0.063-1.069
            c-0.533-0.113-1.078-0.17-1.623-0.167c-0.064,0.352-0.117,0.706-0.162,1.06c-0.126,0.003-0.253,0.013-0.379,0.022
            c-0.841,0.074-1.633,0.307-2.346,0.662c-0.205-0.291-0.419-0.578-0.638-0.859c-0.482,0.254-0.937,0.565-1.351,0.917
            c0.176,0.311,0.363,0.615,0.556,0.914c-0.703,0.63-1.267,1.407-1.646,2.277c-0.344-0.093-0.692-0.176-1.042-0.251
            c-0.206,0.507-0.356,1.034-0.449,1.573c0.336,0.122,0.675,0.236,1.016,0.343c-0.071,0.491-0.088,1.001-0.042,1.518
            c0.04,0.442,0.123,0.873,0.243,1.284c-0.322,0.151-0.641,0.31-0.957,0.48c0.166,0.518,0.392,1.019,0.667,1.488
            c0.337-0.121,0.669-0.251,0.998-0.388c0.5,0.809,1.171,1.494,1.957,2.015c-0.151,0.324-0.292,0.652-0.427,0.982
            c0.461,0.29,0.955,0.528,1.468,0.715c0.181-0.309,0.352-0.623,0.516-0.939c0.822,0.271,1.711,0.383,2.63,0.304
            c0.055-0.006,0.109-0.013,0.164-0.018c0.094,0.343,0.196,0.686,0.306,1.026c0.54-0.075,1.072-0.21,1.585-0.398
            c-0.062-0.351-0.132-0.702-0.21-1.049c0.892-0.356,1.68-0.898,2.324-1.578c0.293,0.206,0.591,0.399,0.895,0.59
            c0.366-0.405,0.688-0.847,0.958-1.321c-0.275-0.229-0.553-0.455-0.837-0.67c0.44-0.819,0.71-1.735,0.77-2.703
            C795.17,631.093,795.525,631.052,795.88,631.001z"/>
        <path fill="#F7941E" d="M730.66,642.763c2.687,2.651,7.014,2.621,9.665-0.068c2.65-2.685,2.621-7.013-0.066-9.663
            c-2.688-2.651-7.015-2.621-9.667,0.066C727.942,635.784,727.972,640.11,730.66,642.763z M731.583,642.049l0.904-0.81l1.761-1.575
            c0.338,0.231,0.726,0.354,1.12,0.372l0.463,2.318l0.238,1.188C734.481,643.715,732.834,643.217,731.583,642.049z M739.503,641.885
            c-0.7,0.711-1.541,1.197-2.433,1.46l-0.239-1.189l-0.463-2.317c0.223-0.106,0.432-0.251,0.616-0.438
            c0.136-0.137,0.249-0.287,0.34-0.448l2.275,0.644l1.167,0.327C740.495,640.637,740.074,641.307,739.503,641.885z M741.042,638.943
            l-1.168-0.329l-2.274-0.643c0.011-0.371-0.071-0.747-0.25-1.085l1.761-1.574l0.903-0.811
            C740.983,635.8,741.327,637.418,741.042,638.943z M739.335,633.743l-0.905,0.81l-1.76,1.575c-0.338-0.231-0.727-0.354-1.12-0.373
            l-0.463-2.316l-0.237-1.188C736.436,632.077,738.084,632.576,739.335,633.743z M731.414,633.908
            c0.701-0.712,1.542-1.196,2.434-1.46l0.239,1.19l0.463,2.315c-0.225,0.107-0.434,0.252-0.618,0.438
            c-0.134,0.138-0.248,0.29-0.338,0.448l-2.275-0.64l-1.168-0.331C730.423,635.156,730.844,634.486,731.414,633.908z
             M731.043,637.181l2.275,0.64c-0.012,0.374,0.071,0.748,0.25,1.085l-1.762,1.576l-0.903,0.81c-0.969-1.298-1.312-2.916-1.028-4.44
            L731.043,637.181z"/>
        <g>
            <polygon fill="#F6891F" points="763.082,640.715 758.054,641.753 759.076,643.338 755.157,645.861 757.125,648.915
                761.042,646.393 762.062,647.978 765.088,643.828 768.112,639.678 		"/>
                <rect x="752.605" y="647.305" transform="matrix(-0.5419 -0.8404 0.8404 -0.5419 618.1162 1633.9843)" fill="#F6891F" width="3.481" height="2.481"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="718.69,629.649 721.463,627.563 723.549,630.335 722.724,630.956 721.697,629.595
                721.281,632.549 720.257,632.403 720.675,629.449 719.312,630.475 		"/>
            <polygon fill="#AAD9B5" points="722.438,626.341 721.777,625.981 722.814,624.069 721.623,624.424 721.408,623.7 723.833,622.98
                724.553,625.408 723.831,625.62 723.477,624.428 		"/>
        </g>
        <polygon fill="#F26522" points="785.478,605.776 782.54,605.776 782.54,602.839 780.495,602.839 780.495,605.776 777.557,605.776
            777.557,607.821 780.495,607.821 780.495,610.76 782.54,610.76 782.54,607.821 785.478,607.821 	"/>
        <polygon fill="#F5EB00" points="704.198,628.579 701.069,628.579 701.069,625.451 698.891,625.451 698.891,628.579
            695.762,628.579 695.762,630.759 698.891,630.759 698.891,633.887 701.069,633.887 701.069,630.759 704.198,630.759 	"/>
        <polygon fill="#8DC63F" points="770.7,599.671 772.35,597.623 765.971,595.802 759.59,593.978 762.73,599.821 765.87,605.668
            767.521,603.618 772.15,607.349 775.331,603.402 	"/>
        <polygon fill="#4EA0B4" points="751.74,600.572 753.515,601.769 749.932,607.081 750.531,607.485 754.115,602.173 755.891,603.371
            756.066,600.874 756.24,598.377 	"/>
        <path fill="#00A0AF" d="M740.438,655.542c-1.353-1.351-3.546-1.351-4.898,0c-1.353,1.354-1.353,3.547,0,4.9s3.545,1.353,4.898,0
            C741.791,659.09,741.791,656.896,740.438,655.542z M738.901,658.905c-0.504,0.505-1.322,0.505-1.825,0
            c-0.504-0.507-0.504-1.321,0-1.826c0.502-0.503,1.321-0.503,1.825,0C739.406,657.584,739.406,658.398,738.901,658.905z"/>
        <polygon fill="#79AD36" points="714.679,668.721 715.489,666.271 722.825,668.698 723.1,667.87 715.764,665.441 716.576,662.989
            713.62,663.599 710.666,664.212 	"/>
        <polygon fill="#17AEB2" points="752.37,639.789 753.187,639.746 753.04,636.99 755.895,636.922 752.388,631.173 749.161,637.086
            752.224,637.009 	"/>
        <g>
            <path fill="#BFE2CE" d="M717.468,615.829l-4.008-0.665l0.667-4.006c0.067-0.401-0.205-0.782-0.606-0.849
                c-0.404-0.068-0.783,0.204-0.851,0.607l-0.666,4.005l-4.006-0.668c-0.401-0.065-0.783,0.206-0.849,0.606
                c-0.067,0.404,0.205,0.784,0.605,0.851l4.008,0.668l-0.668,4.006c-0.066,0.401,0.205,0.782,0.609,0.85
                c0.401,0.066,0.78-0.205,0.848-0.608l0.667-4.006l4.006,0.667c0.403,0.067,0.783-0.205,0.85-0.606
                C718.141,616.278,717.869,615.897,717.468,615.829z"/>
            <path fill="#95A43A" d="M721.271,615.764c0.499-0.08,0.998-0.173,1.496-0.277c-0.023-0.802-0.141-1.6-0.351-2.372
                c-0.506,0.037-1.013,0.09-1.514,0.156c-0.277-0.923-0.708-1.801-1.289-2.598c0.358-0.356,0.708-0.726,1.048-1.104
                c-0.49-0.635-1.058-1.213-1.681-1.715c-0.388,0.331-0.766,0.672-1.132,1.021c-0.797-0.606-1.666-1.052-2.568-1.346
                c0.079-0.5,0.145-1.004,0.198-1.508c-0.77-0.228-1.566-0.361-2.368-0.399c-0.119,0.494-0.224,0.99-0.316,1.49
                c-0.971-0.021-1.941,0.124-2.867,0.423c-0.229-0.451-0.471-0.896-0.725-1.339c-0.756,0.269-1.479,0.628-2.152,1.066
                c0.194,0.47,0.4,0.937,0.619,1.392c-0.714,0.498-1.365,1.11-1.924,1.837c-0.049,0.064-0.099,0.129-0.144,0.195
                c-0.45-0.232-0.91-0.451-1.374-0.657c-0.452,0.66-0.83,1.375-1.114,2.127c0.433,0.267,0.873,0.522,1.318,0.763
                c-0.324,0.929-0.481,1.896-0.48,2.857c-0.5,0.079-0.999,0.174-1.495,0.279c0.021,0.8,0.139,1.599,0.349,2.373
                c0.507-0.04,1.012-0.092,1.515-0.158c0.276,0.921,0.709,1.802,1.289,2.597c-0.359,0.357-0.708,0.727-1.047,1.104
                c0.49,0.635,1.058,1.211,1.68,1.714c0.388-0.332,0.765-0.671,1.132-1.019c0.797,0.604,1.666,1.053,2.568,1.344
                c-0.079,0.5-0.146,1.002-0.198,1.509c0.771,0.227,1.567,0.359,2.367,0.399c0.12-0.494,0.225-0.992,0.316-1.489
                c0.972,0.019,1.942-0.124,2.867-0.426c0.229,0.451,0.471,0.897,0.727,1.34c0.754-0.269,1.479-0.626,2.151-1.067
                c-0.192-0.471-0.401-0.935-0.619-1.393c0.715-0.496,1.365-1.109,1.923-1.835c0.051-0.065,0.098-0.13,0.147-0.195
                c0.451,0.23,0.908,0.451,1.374,0.658c0.45-0.662,0.828-1.377,1.112-2.127c-0.432-0.268-0.872-0.521-1.318-0.766
                C721.113,617.692,721.271,616.728,721.271,615.764z"/>
            <g>
                <path fill="#A9BF38" d="M711.729,621.071c2.927,0.488,5.696-1.491,6.182-4.418c0.487-2.928-1.491-5.696-4.419-6.183
                    c-2.927-0.486-5.695,1.491-6.182,4.418C706.822,617.816,708.801,620.584,711.729,621.071z M713.26,611.869
                    c2.156,0.357,3.612,2.397,3.252,4.552c-0.358,2.155-2.396,3.611-4.552,3.254c-2.156-0.359-3.611-2.398-3.253-4.552
                    C709.067,612.966,711.104,611.51,713.26,611.869z"/>
                <circle fill="#A9BF38" cx="712.61" cy="615.771" r="2.003"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M791.509,610.025c-3.032-0.014-5.504,2.433-5.516,5.464c-0.014,3.034,2.433,5.504,5.465,5.518
                s5.503-2.431,5.516-5.464C796.99,612.51,794.542,610.042,791.509,610.025z M791.465,619.559c-2.233-0.01-4.036-1.828-4.025-4.061
                c0.01-2.232,1.83-4.036,4.063-4.025c2.232,0.012,4.034,1.83,4.023,4.063C795.518,617.769,793.698,619.569,791.465,619.559z"/>
            <circle fill="#CFB52B" cx="791.483" cy="615.517" r="2.046"/>
        </g>
        <circle fill="#8DC63F" cx="707.369" cy="641.281" r="2.805"/>
        <g>
            <path fill="#FEBC11" d="M695.007,652.456c-0.45-0.315-0.715-0.712-0.791-1.188c-0.077-0.479,0.053-0.957,0.39-1.44
                c0.368-0.526,0.807-0.845,1.315-0.949c0.509-0.102,1.006,0.014,1.497,0.355c0.467,0.327,0.735,0.722,0.804,1.185
                c0.069,0.463-0.073,0.948-0.427,1.455c-0.362,0.521-0.798,0.834-1.302,0.936C695.987,652.913,695.491,652.794,695.007,652.456z
                 M701.869,652.469l-8.542,3.456l-1.077-0.749l8.533-3.464L701.869,652.469z M696.849,649.99c-0.432-0.298-0.853-0.148-1.271,0.447
                c-0.394,0.566-0.386,0.992,0.024,1.276c0.417,0.293,0.832,0.146,1.242-0.445C697.243,650.695,697.246,650.269,696.849,649.99z
                 M696.711,658.415c-0.451-0.314-0.715-0.708-0.79-1.186c-0.078-0.478,0.053-0.959,0.391-1.442
                c0.366-0.526,0.805-0.841,1.314-0.947c0.509-0.105,1.006,0.014,1.495,0.356c0.472,0.326,0.742,0.722,0.812,1.18
                c0.067,0.462-0.074,0.943-0.427,1.451c-0.365,0.52-0.8,0.832-1.306,0.939C697.692,658.871,697.196,658.756,696.711,658.415z
                 M698.54,655.944c-0.427-0.297-0.848-0.148-1.266,0.449c-0.394,0.565-0.384,0.992,0.031,1.282
                c0.418,0.292,0.833,0.143,1.244-0.446c0.189-0.275,0.283-0.524,0.275-0.753C698.817,656.249,698.722,656.071,698.54,655.944z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M780.857,643.082l0.601,0.914l-0.615,0.402l-0.583-0.887c-0.573,0.374-1.188,0.591-1.847,0.659
                l-0.768-1.166c0.249,0.024,0.572-0.014,0.97-0.113c0.396-0.099,0.727-0.224,0.99-0.375l-1.009-1.531
                c-0.816,0.188-1.439,0.219-1.867,0.092c-0.426-0.127-0.774-0.396-1.045-0.808c-0.289-0.439-0.38-0.918-0.273-1.431
                c0.107-0.516,0.401-0.968,0.881-1.357l-0.516-0.783l0.615-0.403l0.504,0.767c0.61-0.363,1.116-0.558,1.516-0.586l0.747,1.138
                c-0.55,0.017-1.088,0.163-1.613,0.434l1.049,1.595c0.761-0.183,1.368-0.219,1.822-0.11c0.453,0.11,0.813,0.366,1.08,0.771
                c0.307,0.465,0.405,0.939,0.294,1.419C781.681,642.203,781.37,642.657,780.857,643.082z M777.819,639.803l-0.877-1.335
                c-0.345,0.326-0.405,0.659-0.184,0.995C776.948,639.752,777.302,639.865,777.819,639.803z M779.37,640.82l0.839,1.275
                c0.361-0.324,0.428-0.656,0.205-0.997C780.235,640.824,779.886,640.731,779.37,640.82z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M770.038,624.236l-1.348-0.563l-1.003,2.39l-2.258-0.946l1.001-2.39l-4.898-2.054l0.659-1.575l7.769-5.299
                l2.439,1.021l-2.97,7.089l1.35,0.564L770.038,624.236z M769.134,616.365l-0.047-0.021c-0.152,0.157-0.416,0.391-0.792,0.7
                l-3.97,2.725l2.846,1.192l1.482-3.536C768.785,617.113,768.944,616.759,769.134,616.365z"/>
        </g>
    </g>
    <g>
        <path fill="#262425" d="M728.795,803.872c0-6.9,3.545-13.018,9.005-16.807c1.097-10.533,10.482-18.761,21.897-18.761
            c0.353,0,0.701,0.009,1.05,0.024c2.766-2.813,6.344-4.9,10.381-5.927c1.871-0.477,3.8-0.714,5.728-0.714
            c2.893,0,5.654,0.529,8.183,1.489c0.136-0.052,0.271-0.102,0.407-0.15c0.005-0.003,0.011-0.005,0.018-0.006
            c0.137-0.049,0.275-0.097,0.414-0.145l0,0c2.297-0.769,4.77-1.188,7.345-1.188c1.812,0,3.572,0.208,5.257,0.6
            c1.977-0.867,4.179-1.352,6.499-1.352c5.963,0,11.136,3.19,13.726,7.862c5.162,0.386,9.513,3.487,11.466,7.794
            c5.33,1.197,9.524,5.863,9.524,11.413c0,0.534-0.038,1.057-0.11,1.569c1.992,1.933,3.431,4.417,4.05,7.131
            c0.619,2.703,0.409,5.574-0.595,8.16c-0.554,1.426-1.342,2.759-2.314,3.941c-0.698,0.87-1.679,1.479-2.376,2.314
            c-1.635,1.278-3.551,2.244-5.646,2.803c-0.03,7.037-6.064,12.731-13.504,12.731c-0.073,0-0.145,0-0.217-0.003
            c-1.893,1.855-4.549,3.01-7.488,3.01c-3.406,0-6.429-1.55-8.328-3.942c-3.428,7.285-11.143,12.363-20.112,12.363
            c-3.682,0-7.152-0.854-10.2-2.367c-2.941,4.491-8.195,7.48-14.188,7.48c-5.458,0-10.306-2.479-13.348-6.318
            c-8.613-0.08-15.57-6.714-15.57-14.885c0-2.728,0.775-5.286,2.13-7.484C729.919,811.396,728.795,807.758,728.795,803.872z"/>
        <path fill="#00A0AF" d="M791.139,827.105c-0.603-1.424-1.464-2.698-2.558-3.794c-1.097-1.096-2.373-1.956-3.794-2.559
            c-1.473-0.623-3.038-0.939-4.646-0.939h-30.704c-4.917,0-8.918-3.999-8.918-8.917v-3.728h-3.019v3.728
            c0,1.61,0.314,3.174,0.939,4.647c0.601,1.422,1.461,2.698,2.557,3.797c1.095,1.094,2.372,1.953,3.792,2.556
            c1.473,0.62,3.036,0.939,4.648,0.939h30.704c4.917,0,8.917,4,8.917,8.914v39.533h3.02V831.75
            C792.077,830.14,791.761,828.577,791.139,827.105z"/>
        <path fill="#95A43A" d="M786.233,803.204c-0.602-1.419-1.462-2.697-2.557-3.79c-1.097-1.099-2.374-1.957-3.793-2.561
            c-1.475-0.622-3.037-0.938-4.648-0.938h-9.645v3.021h9.645c4.917,0,8.917,3.998,8.917,8.916v63.432h3.021v-63.432
            C787.173,806.241,786.855,804.676,786.233,803.204z"/>
        <path fill="#F7941E" d="M809.374,784.167v42.214c0,4.918-4,8.917-8.917,8.917h-9.23c-1.611,0-3.175,0.316-4.648,0.937
            c-1.42,0.603-2.697,1.464-3.793,2.56c-1.097,1.097-1.956,2.371-2.557,3.795c-0.624,1.471-0.938,3.034-0.938,4.647v24.046h3.019
            v-24.046c0-4.919,4-8.919,8.918-8.919h9.23c1.611,0,3.175-0.316,4.646-0.939c1.422-0.603,2.698-1.462,3.794-2.556
            c1.096-1.098,1.956-2.373,2.557-3.793c0.624-1.475,0.939-3.039,0.939-4.648v-42.214H809.374z"/>
        <path fill="#FEBC11" d="M755.83,800.276v22.122c0,4.917,4.001,8.916,8.917,8.916h0.793c1.611,0,3.174,0.317,4.646,0.94
            c1.422,0.6,2.697,1.46,3.794,2.557c1.096,1.096,1.957,2.372,2.558,3.793c0.623,1.475,0.938,3.035,0.938,4.647v28.031h-3.019
            v-28.031c0-4.917-4.001-8.92-8.917-8.92l-0.793,0.004c-1.61,0-3.174-0.315-4.647-0.939c-1.42-0.602-2.697-1.463-3.792-2.557
            c-1.096-1.097-1.958-2.373-2.558-3.794c-0.623-1.475-0.939-3.037-0.939-4.647v-22.122H755.83z"/>
        <path fill="#F26522" d="M826.061,801.242v9.286c0,4.918-4,8.918-8.917,8.918l-11.321-0.007c-1.612,0-3.175,0.316-4.647,0.939
            c-1.421,0.602-2.698,1.46-3.794,2.558c-1.095,1.096-1.956,2.371-2.556,3.794c-0.625,1.474-0.939,3.037-0.939,4.646v39.907h3.02
            v-39.907c0-4.915,3.999-8.915,8.915-8.915l1.473,0.006h9.851c1.609,0,3.174-0.317,4.646-0.94c1.422-0.602,2.698-1.462,3.794-2.556
            c1.096-1.1,1.957-2.374,2.557-3.796c0.623-1.474,0.938-3.036,0.938-4.646v-9.286H826.061z"/>
        <path fill="#F7941E" d="M791.298,783.63c3.161-1.96,7.61-3.181,12.539-3.181c9.596,0,17.373,4.629,17.373,10.337
            c0,5.454-7.101,9.922-16.104,10.309c-0.42,0.02-0.842,0.027-1.269,0.027c-1.084,0-2.144-0.059-3.172-0.171
            c-0.1,0.479-0.151,0.973-0.151,1.479c0,1.531,0.479,2.95,1.294,4.116c-2.824-0.816-4.929-3.321-5.161-6.35
            c-6.009-1.624-10.188-5.229-10.188-9.411C786.46,788.009,788.302,785.488,791.298,783.63z"/>
        <g>
            <path fill="#00ADBA" d="M785.603,778.278c-0.033-1.445-0.357-2.906-1.003-4.3c-0.04-0.086-0.08-0.169-0.12-0.252
                c0.492-0.344,0.977-0.701,1.456-1.069c-0.423-0.806-0.936-1.564-1.524-2.258c-0.526,0.298-1.042,0.608-1.549,0.932
                c-1.068-1.195-2.375-2.122-3.817-2.743c0.158-0.574,0.3-1.156,0.431-1.743c-0.849-0.338-1.734-0.591-2.64-0.744
                c-0.212,0.565-0.411,1.129-0.591,1.7c-1.547-0.222-3.156-0.12-4.724,0.346c-0.25-0.539-0.519-1.071-0.799-1.597
                c-0.875,0.281-1.717,0.658-2.514,1.117c0.199,0.563,0.413,1.118,0.641,1.67c-1.398,0.854-2.552,1.98-3.421,3.273
                c-0.543-0.247-1.094-0.482-1.651-0.705c-0.493,0.774-0.903,1.599-1.217,2.458c0.52,0.298,1.045,0.583,1.577,0.857
                c-0.505,1.488-0.693,3.082-0.522,4.671c-0.58,0.158-1.157,0.332-1.732,0.517c0.121,0.903,0.342,1.792,0.654,2.647
                c0.594-0.105,1.187-0.225,1.772-0.357c0.078,0.195,0.162,0.391,0.252,0.582c0.593,1.279,1.406,2.389,2.374,3.302
                c-0.345,0.492-0.681,0.991-1.004,1.498c0.679,0.61,1.425,1.147,2.219,1.597c0.392-0.459,0.771-0.928,1.137-1.403
                c1.396,0.744,2.948,1.183,4.544,1.279c0.048,0.592,0.114,1.185,0.193,1.777c0.918,0.031,1.842-0.034,2.748-0.2
                c0.004-0.599-0.004-1.198-0.03-1.791c0.823-0.172,1.64-0.438,2.435-0.808c0.682-0.317,1.316-0.693,1.9-1.12
                c0.424,0.418,0.856,0.828,1.302,1.228c0.73-0.56,1.398-1.204,1.989-1.906c-0.382-0.457-0.777-0.905-1.182-1.343
                c1-1.252,1.712-2.698,2.096-4.23c0.597,0.047,1.198,0.084,1.8,0.104c0.2-0.89,0.3-1.803,0.304-2.716
                C786.792,778.443,786.198,778.352,785.603,778.278z M777.917,785.785c-4.036,1.874-8.823,0.12-10.697-3.914
                c-1.872-4.033-0.12-8.821,3.915-10.695c4.034-1.873,8.822-0.121,10.696,3.913C783.705,779.125,781.952,783.91,777.917,785.785z"/>
                <ellipse transform="matrix(-0.4212 -0.907 0.907 -0.4212 394.6593 1809.0994)" fill="#00ADBA" cx="774.614" cy="778.614" rx="3.481" ry="3.523"/>
        </g>
        <polygon fill="#FAA21B" points="745.431,827.255 744.318,826.144 742.824,829.824 741.331,833.5 745.01,832.009 748.688,830.515
            747.575,829.401 750.091,826.885 747.946,824.742 	"/>
        <path fill="#008FAF" d="M761.596,807.57c0,0-2.888,4.22-2.888,5.815c0,1.597,1.293,2.89,2.888,2.89c1.597,0,2.891-1.293,2.891-2.89
            C764.487,811.79,761.596,807.57,761.596,807.57z"/>
        <path fill="#FEBC11" d="M835.23,801.977c0.019-0.546-0.021-1.09-0.118-1.626c-0.357-0.001-0.714,0.006-1.071,0.023
            c-0.195-0.952-0.596-1.821-1.147-2.563c0.25-0.254,0.496-0.514,0.736-0.779c-0.337-0.43-0.718-0.823-1.137-1.171
            c-0.274,0.229-0.543,0.464-0.805,0.706c-0.735-0.58-1.596-1.004-2.528-1.227c0.032-0.355,0.051-0.713,0.063-1.069
            c-0.533-0.113-1.078-0.17-1.623-0.167c-0.064,0.352-0.117,0.706-0.162,1.06c-0.126,0.003-0.253,0.013-0.379,0.022
            c-0.841,0.074-1.633,0.307-2.346,0.662c-0.205-0.291-0.419-0.578-0.638-0.859c-0.482,0.254-0.937,0.565-1.351,0.917
            c0.176,0.311,0.363,0.615,0.556,0.914c-0.703,0.63-1.267,1.406-1.646,2.277c-0.344-0.093-0.692-0.176-1.042-0.251
            c-0.206,0.507-0.356,1.034-0.449,1.573c0.336,0.122,0.675,0.236,1.016,0.343c-0.071,0.491-0.088,1.001-0.042,1.518
            c0.04,0.442,0.123,0.873,0.243,1.284c-0.322,0.151-0.641,0.31-0.957,0.48c0.166,0.518,0.392,1.019,0.667,1.488
            c0.337-0.121,0.669-0.251,0.998-0.388c0.5,0.809,1.171,1.494,1.957,2.015c-0.151,0.324-0.292,0.652-0.427,0.982
            c0.461,0.29,0.955,0.528,1.468,0.715c0.181-0.309,0.352-0.623,0.516-0.939c0.822,0.271,1.711,0.383,2.63,0.304
            c0.055-0.006,0.109-0.013,0.164-0.018c0.094,0.343,0.196,0.686,0.306,1.026c0.54-0.075,1.072-0.21,1.585-0.398
            c-0.062-0.351-0.132-0.702-0.21-1.049c0.892-0.356,1.68-0.898,2.324-1.578c0.293,0.205,0.591,0.399,0.895,0.59
            c0.366-0.405,0.688-0.847,0.958-1.321c-0.275-0.229-0.553-0.455-0.837-0.67c0.44-0.819,0.71-1.735,0.77-2.703
            C834.521,802.069,834.876,802.028,835.23,801.977z"/>
        <path fill="#F7941E" d="M770.011,813.739c2.687,2.651,7.014,2.621,9.665-0.068c2.65-2.685,2.621-7.013-0.066-9.663
            c-2.688-2.651-7.015-2.621-9.667,0.066C767.292,806.76,767.322,811.086,770.011,813.739z M770.934,813.025l0.904-0.81l1.761-1.575
            c0.338,0.231,0.726,0.354,1.12,0.372l0.463,2.318l0.238,1.188C773.832,814.691,772.185,814.193,770.934,813.025z M778.854,812.861
            c-0.7,0.711-1.541,1.197-2.433,1.46l-0.239-1.189l-0.463-2.317c0.223-0.106,0.432-0.251,0.616-0.438
            c0.136-0.137,0.249-0.287,0.34-0.448l2.275,0.644l1.167,0.327C779.846,811.613,779.425,812.283,778.854,812.861z M780.393,809.919
            l-1.168-0.329l-2.274-0.643c0.011-0.371-0.071-0.747-0.25-1.085l1.761-1.574l0.903-0.811
            C780.333,806.776,780.677,808.394,780.393,809.919z M778.686,804.719l-0.905,0.81l-1.76,1.575c-0.338-0.231-0.727-0.354-1.12-0.373
            l-0.463-2.316l-0.237-1.188C775.787,803.053,777.435,803.551,778.686,804.719z M770.765,804.884
            c0.701-0.712,1.542-1.196,2.434-1.46l0.239,1.19l0.463,2.316c-0.225,0.107-0.434,0.251-0.618,0.437
            c-0.134,0.139-0.248,0.29-0.338,0.448l-2.275-0.64l-1.168-0.331C769.773,806.132,770.195,805.462,770.765,804.884z
             M770.394,808.157l2.275,0.64c-0.012,0.374,0.071,0.748,0.25,1.085l-1.762,1.576l-0.903,0.81c-0.969-1.298-1.312-2.916-1.028-4.44
            L770.394,808.157z"/>
        <g>
            <polygon fill="#F6891F" points="802.433,811.691 797.404,812.729 798.426,814.314 794.507,816.837 796.476,819.891
                800.393,817.369 801.413,818.954 804.438,814.804 807.463,810.654 		"/>
                <rect x="791.955" y="818.281" transform="matrix(-0.5419 -0.8404 0.8404 -0.5419 535.1024 1930.6913)" fill="#F6891F" width="3.481" height="2.481"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="758.041,800.625 760.813,798.539 762.9,801.311 762.075,801.932 761.048,800.571 760.632,803.525
                759.607,803.379 760.025,800.425 758.663,801.451 		"/>
            <polygon fill="#AAD9B5" points="761.789,797.317 761.128,796.958 762.165,795.045 760.973,795.4 760.759,794.676 763.184,793.957
                763.903,796.384 763.182,796.596 762.827,795.404 		"/>
        </g>
        <polygon fill="#F26522" points="824.829,776.752 821.891,776.752 821.891,773.815 819.845,773.815 819.845,776.752
            816.907,776.752 816.907,778.797 819.845,778.797 819.845,781.736 821.891,781.736 821.891,778.797 824.829,778.797 	"/>
        <polygon fill="#F5EB00" points="743.549,799.555 740.419,799.555 740.419,796.427 738.242,796.427 738.242,799.555
            735.112,799.555 735.112,801.735 738.242,801.735 738.242,804.863 740.419,804.863 740.419,801.735 743.549,801.735 	"/>
        <polygon fill="#8DC63F" points="810.051,770.647 811.7,768.599 805.321,766.778 798.94,764.954 802.081,770.797 805.22,776.644
            806.871,774.594 811.501,778.325 814.682,774.378 	"/>
        <polygon fill="#4EA0B4" points="791.091,771.548 792.866,772.745 789.282,778.057 789.881,778.461 793.466,773.149
            795.241,774.347 795.417,771.85 795.591,769.353 	"/>
        <path fill="#00A0AF" d="M779.789,826.518c-1.353-1.351-3.546-1.351-4.898,0c-1.353,1.354-1.353,3.548,0,4.9s3.545,1.353,4.898,0
            C781.141,830.066,781.141,827.873,779.789,826.518z M778.252,829.881c-0.504,0.505-1.322,0.505-1.825,0
            c-0.504-0.507-0.504-1.321,0-1.826c0.502-0.504,1.321-0.504,1.825,0C778.756,828.56,778.756,829.375,778.252,829.881z"/>
        <polygon fill="#79AD36" points="754.029,839.697 754.84,837.247 762.175,839.674 762.45,838.846 755.115,836.417 755.926,833.965
            752.971,834.575 750.016,835.188 	"/>
        <polygon fill="#17AEB2" points="791.721,810.765 792.537,810.722 792.391,807.966 795.246,807.898 791.739,802.149
            788.512,808.062 791.575,807.985 	"/>
        <g>
            <path fill="#BFE2CE" d="M756.818,786.805l-4.008-0.665l0.667-4.006c0.067-0.401-0.205-0.782-0.606-0.849
                c-0.404-0.068-0.783,0.204-0.851,0.607l-0.666,4.005l-4.006-0.668c-0.401-0.065-0.783,0.206-0.849,0.606
                c-0.067,0.404,0.205,0.784,0.605,0.851l4.008,0.668l-0.668,4.005c-0.066,0.401,0.205,0.782,0.609,0.85
                c0.401,0.066,0.78-0.205,0.848-0.608l0.667-4.006l4.006,0.667c0.403,0.067,0.783-0.205,0.85-0.606
                C757.491,787.254,757.22,786.874,756.818,786.805z"/>
            <path fill="#95A43A" d="M760.622,786.74c0.499-0.08,0.998-0.173,1.496-0.277c-0.023-0.803-0.141-1.6-0.351-2.372
                c-0.506,0.037-1.013,0.09-1.514,0.156c-0.277-0.923-0.708-1.801-1.289-2.598c0.358-0.356,0.708-0.726,1.048-1.104
                c-0.49-0.636-1.058-1.213-1.681-1.715c-0.388,0.331-0.766,0.672-1.132,1.021c-0.797-0.606-1.666-1.052-2.568-1.346
                c0.079-0.5,0.145-1.004,0.198-1.508c-0.77-0.228-1.566-0.361-2.368-0.399c-0.119,0.494-0.224,0.99-0.316,1.49
                c-0.971-0.021-1.941,0.125-2.867,0.423c-0.229-0.451-0.471-0.896-0.725-1.339c-0.756,0.269-1.479,0.628-2.152,1.066
                c0.194,0.47,0.4,0.937,0.619,1.392c-0.714,0.498-1.365,1.11-1.924,1.837c-0.049,0.064-0.099,0.129-0.144,0.195
                c-0.45-0.232-0.91-0.451-1.374-0.657c-0.452,0.66-0.83,1.375-1.114,2.127c0.433,0.267,0.873,0.522,1.318,0.763
                c-0.324,0.929-0.481,1.896-0.48,2.857c-0.5,0.079-0.999,0.174-1.495,0.279c0.021,0.8,0.139,1.599,0.349,2.373
                c0.507-0.04,1.012-0.092,1.515-0.158c0.276,0.921,0.709,1.802,1.289,2.597c-0.359,0.357-0.708,0.727-1.047,1.104
                c0.49,0.635,1.058,1.211,1.68,1.714c0.388-0.332,0.765-0.671,1.132-1.019c0.797,0.604,1.666,1.053,2.568,1.344
                c-0.079,0.5-0.146,1.002-0.198,1.509c0.771,0.227,1.567,0.359,2.367,0.399c0.12-0.494,0.225-0.992,0.316-1.489
                c0.972,0.019,1.942-0.124,2.867-0.426c0.229,0.451,0.471,0.897,0.727,1.34c0.754-0.27,1.479-0.626,2.151-1.067
                c-0.192-0.471-0.401-0.935-0.619-1.393c0.715-0.496,1.365-1.109,1.923-1.835c0.051-0.065,0.098-0.13,0.147-0.195
                c0.451,0.23,0.908,0.451,1.374,0.658c0.45-0.662,0.828-1.377,1.112-2.127c-0.432-0.268-0.872-0.521-1.318-0.766
                C760.464,788.668,760.622,787.704,760.622,786.74z"/>
            <g>
                <path fill="#A9BF38" d="M751.08,792.047c2.927,0.488,5.696-1.491,6.182-4.418c0.487-2.928-1.491-5.696-4.419-6.183
                    c-2.927-0.486-5.695,1.491-6.182,4.418C746.173,788.792,748.152,791.56,751.08,792.047z M752.61,782.845
                    c2.156,0.357,3.612,2.397,3.252,4.552c-0.358,2.155-2.396,3.611-4.552,3.253c-2.156-0.358-3.611-2.397-3.253-4.552
                    C748.417,783.942,750.455,782.486,752.61,782.845z"/>
                <circle fill="#A9BF38" cx="751.961" cy="786.747" r="2.003"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M830.86,781.001c-3.032-0.014-5.504,2.433-5.516,5.464c-0.014,3.034,2.433,5.504,5.465,5.518
                s5.503-2.431,5.516-5.465C836.34,783.486,833.892,781.018,830.86,781.001z M830.816,790.535c-2.233-0.01-4.036-1.828-4.025-4.061
                c0.01-2.232,1.83-4.036,4.063-4.025c2.232,0.012,4.034,1.83,4.023,4.063C834.868,788.745,833.049,790.545,830.816,790.535z"/>
            <circle fill="#CFB52B" cx="830.834" cy="786.493" r="2.046"/>
        </g>
        <circle fill="#8DC63F" cx="746.719" cy="812.257" r="2.805"/>
        <g>
            <path fill="#FEBC11" d="M734.357,823.432c-0.45-0.315-0.715-0.712-0.791-1.188c-0.077-0.479,0.053-0.957,0.39-1.44
                c0.368-0.526,0.807-0.845,1.315-0.949c0.509-0.102,1.006,0.014,1.497,0.355c0.467,0.327,0.735,0.722,0.804,1.185
                c0.069,0.463-0.073,0.948-0.427,1.455c-0.362,0.521-0.798,0.834-1.302,0.936C735.337,823.889,734.842,823.77,734.357,823.432z
                 M741.22,823.445l-8.542,3.456l-1.077-0.749l8.533-3.464L741.22,823.445z M736.2,820.966c-0.432-0.298-0.853-0.148-1.271,0.447
                c-0.394,0.566-0.386,0.992,0.024,1.276c0.417,0.293,0.832,0.146,1.242-0.445C736.594,821.671,736.596,821.245,736.2,820.966z
                 M736.062,829.391c-0.451-0.314-0.715-0.708-0.79-1.186c-0.078-0.478,0.053-0.959,0.391-1.442
                c0.366-0.526,0.805-0.841,1.314-0.947c0.509-0.105,1.006,0.014,1.495,0.356c0.472,0.326,0.742,0.722,0.812,1.18
                c0.067,0.462-0.074,0.943-0.427,1.451c-0.365,0.52-0.8,0.832-1.306,0.939C737.043,829.847,736.547,829.732,736.062,829.391z
                 M737.89,826.92c-0.427-0.297-0.848-0.148-1.266,0.449c-0.394,0.565-0.384,0.992,0.031,1.282c0.418,0.292,0.833,0.143,1.244-0.446
                c0.189-0.275,0.283-0.524,0.275-0.753C738.168,827.225,738.073,827.047,737.89,826.92z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M820.208,814.058l0.601,0.914l-0.615,0.402l-0.583-0.887c-0.573,0.374-1.188,0.591-1.847,0.659
                l-0.768-1.166c0.249,0.024,0.572-0.014,0.97-0.113c0.396-0.099,0.727-0.224,0.99-0.375l-1.009-1.531
                c-0.816,0.188-1.439,0.219-1.867,0.092c-0.426-0.127-0.774-0.396-1.045-0.808c-0.289-0.439-0.38-0.918-0.273-1.431
                c0.107-0.516,0.401-0.968,0.881-1.357l-0.516-0.783l0.615-0.403l0.504,0.767c0.61-0.363,1.116-0.558,1.516-0.586l0.747,1.138
                c-0.55,0.017-1.088,0.163-1.613,0.434l1.049,1.595c0.761-0.183,1.368-0.219,1.822-0.109c0.453,0.109,0.813,0.366,1.08,0.771
                c0.307,0.465,0.405,0.939,0.294,1.419C821.032,813.179,820.72,813.633,820.208,814.058z M817.169,810.779l-0.877-1.335
                c-0.345,0.326-0.405,0.659-0.184,0.995C816.298,810.728,816.652,810.841,817.169,810.779z M818.721,811.796l0.839,1.275
                c0.361-0.324,0.428-0.656,0.205-0.997C819.586,811.8,819.236,811.708,818.721,811.796z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M809.389,795.212l-1.348-0.563l-1.003,2.39l-2.258-0.947l1.001-2.39l-4.898-2.054l0.659-1.575l7.769-5.299
                l2.439,1.021l-2.97,7.089l1.35,0.564L809.389,795.212z M808.485,787.341l-0.047-0.021c-0.152,0.157-0.416,0.391-0.792,0.7
                l-3.97,2.725l2.846,1.192l1.482-3.536C808.136,788.089,808.295,787.735,808.485,787.341z"/>
        </g>
    </g>
    <text transform="matrix(1 0 0 1 488.0137 295.6348)" font-family="\'Bahnschrift\'" font-size="20.3685">QuantiModo</text>
    <g>
        <path fill="#262425" d="M460.84,282.735c0-1.609,0.827-3.036,2.101-3.92c0.255-2.457,2.445-4.376,5.107-4.376
            c0.083,0,0.163,0.002,0.245,0.006c0.646-0.656,1.48-1.143,2.422-1.382c0.436-0.111,0.886-0.166,1.336-0.166
            c0.675,0,1.318,0.123,1.908,0.347c0.032-0.012,0.063-0.023,0.095-0.035c0.001,0,0.003-0.001,0.005-0.001
            c0.032-0.012,0.063-0.022,0.097-0.034l0,0c0.536-0.18,1.112-0.277,1.712-0.277c0.423,0,0.833,0.048,1.227,0.14
            c0.461-0.203,0.975-0.315,1.516-0.315c1.391,0,2.597,0.744,3.201,1.834c1.205,0.09,2.22,0.813,2.675,1.818
            c1.243,0.279,2.222,1.368,2.222,2.663c0,0.124-0.009,0.246-0.026,0.366c0.465,0.451,0.801,1.031,0.945,1.664
            c0.145,0.63,0.095,1.3-0.139,1.903c-0.129,0.333-0.313,0.644-0.54,0.919c-0.163,0.203-0.392,0.346-0.554,0.541
            c-0.382,0.298-0.829,0.523-1.317,0.653c-0.007,1.642-1.415,2.97-3.15,2.97c-0.018,0-0.034,0-0.051-0.001
            c-0.441,0.433-1.061,0.702-1.747,0.702c-0.794,0-1.5-0.362-1.943-0.919c-0.8,1.699-2.599,2.883-4.691,2.883
            c-0.859,0-1.668-0.199-2.379-0.552c-0.686,1.048-1.912,1.745-3.31,1.745c-1.273,0-2.404-0.578-3.113-1.474
            c-2.009-0.019-3.631-1.566-3.631-3.472c0-0.636,0.181-1.233,0.497-1.746C461.103,284.491,460.84,283.643,460.84,282.735z"/>
        <path fill="#00A0AF" d="M475.383,288.155c-0.141-0.332-0.342-0.63-0.597-0.885c-0.256-0.256-0.553-0.456-0.885-0.597
            c-0.343-0.146-0.708-0.219-1.083-0.219h-7.162c-1.147,0-2.08-0.933-2.08-2.08v-0.87h-0.704v0.87c0,0.375,0.073,0.74,0.219,1.083
            c0.139,0.332,0.34,0.63,0.596,0.886c0.256,0.255,0.553,0.456,0.884,0.596c0.344,0.145,0.708,0.219,1.084,0.219h7.162
            c1.147,0,2.08,0.933,2.08,2.08v9.221h0.705v-9.221C475.602,288.863,475.528,288.499,475.383,288.155z"/>
        <path fill="#95A43A" d="M474.239,282.58c-0.141-0.331-0.341-0.629-0.596-0.884c-0.256-0.257-0.554-0.457-0.885-0.598
            c-0.344-0.146-0.708-0.219-1.084-0.219h-2.25v0.705h2.25c1.147,0,2.08,0.933,2.08,2.08v14.796h0.705v-14.796
            C474.458,283.289,474.384,282.924,474.239,282.58z"/>
        <path fill="#F7941E" d="M479.637,278.14v9.847c0,1.147-0.933,2.08-2.08,2.08h-2.153c-0.375,0-0.741,0.074-1.084,0.219
            c-0.332,0.141-0.629,0.341-0.885,0.597c-0.255,0.256-0.456,0.553-0.596,0.885c-0.146,0.343-0.219,0.708-0.219,1.084v5.609h0.704
            v-5.609c0-1.147,0.933-2.081,2.08-2.081h2.153c0.375,0,0.74-0.074,1.084-0.219c0.332-0.141,0.629-0.341,0.884-0.597
            c0.257-0.256,0.457-0.553,0.597-0.884c0.145-0.344,0.219-0.709,0.219-1.084v-9.847H479.637z"/>
        <path fill="#FEBC11" d="M467.147,281.897v5.16c0,1.147,0.934,2.08,2.08,2.08h0.185c0.375,0,0.74,0.074,1.083,0.22
            c0.332,0.14,0.629,0.341,0.885,0.597c0.256,0.255,0.457,0.553,0.597,0.884c0.146,0.344,0.218,0.708,0.218,1.084v6.539h-0.704
            v-6.539c0-1.147-0.934-2.081-2.08-2.081l-0.185,0.001c-0.376,0-0.741-0.074-1.084-0.219c-0.331-0.14-0.629-0.341-0.884-0.596
            c-0.255-0.256-0.457-0.554-0.597-0.885c-0.145-0.344-0.219-0.708-0.219-1.084v-5.16H467.147z"/>
        <path fill="#F26522" d="M483.529,282.123v2.166c0,1.147-0.933,2.081-2.08,2.081l-2.641-0.002c-0.376,0-0.741,0.074-1.084,0.219
            c-0.332,0.141-0.629,0.341-0.886,0.597c-0.255,0.256-0.456,0.553-0.595,0.885c-0.146,0.344-0.219,0.709-0.219,1.084v9.309h0.704
            v-9.309c0-1.146,0.933-2.08,2.079-2.08l0.344,0.001h2.298c0.375,0,0.74-0.074,1.083-0.22c0.332-0.14,0.629-0.341,0.885-0.596
            c0.255-0.257,0.457-0.554,0.597-0.886c0.145-0.344,0.218-0.708,0.218-1.084v-2.166H483.529z"/>
        <path fill="#F7941E" d="M475.42,278.015c0.738-0.457,1.776-0.742,2.925-0.742c2.238,0,4.053,1.08,4.053,2.411
            c0,1.272-1.656,2.314-3.757,2.404c-0.098,0.005-0.196,0.007-0.296,0.007c-0.252,0-0.5-0.014-0.739-0.04
            c-0.024,0.111-0.035,0.227-0.035,0.345c0,0.358,0.111,0.688,0.301,0.96c-0.659-0.19-1.149-0.774-1.204-1.481
            c-1.402-0.379-2.376-1.22-2.376-2.195C474.292,279.036,474.721,278.447,475.42,278.015z"/>
        <g>
            <path fill="#00ADBA" d="M474.092,276.766c-0.008-0.337-0.083-0.678-0.234-1.003c-0.009-0.02-0.018-0.039-0.028-0.059
                c0.115-0.08,0.228-0.164,0.339-0.25c-0.098-0.188-0.218-0.364-0.355-0.526c-0.123,0.069-0.243,0.142-0.361,0.217
                c-0.25-0.279-0.555-0.496-0.891-0.64c0.037-0.135,0.07-0.27,0.101-0.407c-0.198-0.079-0.405-0.138-0.616-0.173
                c-0.05,0.132-0.096,0.263-0.138,0.396c-0.361-0.051-0.736-0.028-1.102,0.081c-0.059-0.125-0.122-0.25-0.187-0.372
                c-0.204,0.065-0.4,0.153-0.586,0.26c0.046,0.131,0.096,0.261,0.149,0.39c-0.327,0.199-0.596,0.462-0.798,0.763
                c-0.127-0.057-0.255-0.112-0.385-0.164c-0.115,0.181-0.21,0.373-0.284,0.573c0.121,0.069,0.244,0.136,0.368,0.2
                c-0.118,0.347-0.162,0.719-0.122,1.089c-0.136,0.037-0.271,0.078-0.405,0.121c0.029,0.211,0.08,0.418,0.153,0.617
                c0.139-0.024,0.277-0.052,0.414-0.083c0.018,0.045,0.038,0.091,0.059,0.136c0.138,0.298,0.328,0.557,0.554,0.77
                c-0.081,0.115-0.159,0.231-0.234,0.35c0.158,0.142,0.332,0.268,0.518,0.373c0.091-0.107,0.18-0.217,0.265-0.328
                c0.326,0.174,0.688,0.276,1.061,0.298c0.011,0.138,0.026,0.276,0.044,0.415c0.214,0.007,0.429-0.009,0.641-0.047
                c0.001-0.14-0.001-0.28-0.007-0.418c0.192-0.04,0.382-0.103,0.568-0.188c0.159-0.074,0.308-0.162,0.443-0.261
                c0.099,0.097,0.2,0.193,0.304,0.287c0.17-0.131,0.325-0.281,0.463-0.445c-0.088-0.106-0.181-0.211-0.275-0.313
                c0.233-0.292,0.399-0.63,0.489-0.987c0.14,0.011,0.279,0.02,0.42,0.024c0.046-0.208,0.07-0.42,0.071-0.634
                C474.369,276.805,474.23,276.783,474.092,276.766z M472.299,278.517c-0.941,0.437-2.059,0.028-2.495-0.914
                c-0.437-0.94-0.028-2.058,0.912-2.495c0.942-0.437,2.059-0.028,2.496,0.913C473.649,276.963,473.24,278.08,472.299,278.517z"/>
                <ellipse transform="matrix(-0.4212 -0.907 0.907 -0.4212 419.0309 821.1099)" fill="#00ADBA" cx="471.528" cy="276.844" rx="0.812" ry="0.822"/>
        </g>
        <polygon fill="#FAA21B" points="464.722,288.19 464.462,287.931 464.113,288.79 463.765,289.647 464.623,289.299 465.48,288.951
            465.222,288.69 465.809,288.104 465.308,287.604 	"/>
        <path fill="#008FAF" d="M468.492,283.598c0,0-0.673,0.985-0.673,1.357c0,0.373,0.302,0.674,0.673,0.674
            c0.373,0,0.674-0.301,0.674-0.674C469.166,284.583,468.492,283.598,468.492,283.598z"/>
        <path fill="#FEBC11" d="M485.668,282.294c0.004-0.127-0.005-0.254-0.028-0.379c-0.083,0-0.167,0.001-0.25,0.005
            c-0.045-0.222-0.139-0.425-0.267-0.598c0.058-0.06,0.115-0.12,0.171-0.182c-0.079-0.101-0.167-0.192-0.265-0.273
            c-0.064,0.054-0.126,0.108-0.188,0.165c-0.172-0.136-0.373-0.234-0.59-0.287c0.007-0.083,0.012-0.166,0.014-0.249
            c-0.124-0.027-0.25-0.04-0.377-0.04c-0.016,0.082-0.028,0.165-0.039,0.248c-0.029,0-0.059,0.002-0.088,0.005
            c-0.197,0.017-0.382,0.071-0.547,0.154c-0.048-0.068-0.098-0.135-0.149-0.201c-0.113,0.059-0.219,0.132-0.315,0.214
            c0.041,0.072,0.085,0.143,0.13,0.213c-0.165,0.147-0.296,0.328-0.384,0.531c-0.08-0.021-0.162-0.041-0.243-0.059
            c-0.049,0.118-0.083,0.241-0.105,0.367c0.078,0.028,0.158,0.055,0.236,0.08c-0.016,0.114-0.02,0.233-0.009,0.354
            c0.008,0.103,0.028,0.203,0.056,0.299c-0.075,0.035-0.149,0.072-0.223,0.111c0.039,0.121,0.092,0.238,0.156,0.348
            c0.079-0.028,0.156-0.058,0.232-0.09c0.117,0.188,0.273,0.348,0.456,0.47c-0.034,0.076-0.067,0.152-0.099,0.229
            c0.107,0.068,0.223,0.123,0.342,0.167c0.043-0.072,0.083-0.146,0.121-0.219c0.192,0.063,0.399,0.089,0.614,0.071
            c0.013-0.001,0.025-0.003,0.038-0.004c0.022,0.08,0.046,0.16,0.071,0.239c0.126-0.018,0.25-0.049,0.37-0.093
            c-0.014-0.082-0.031-0.164-0.049-0.244c0.208-0.083,0.392-0.21,0.542-0.368c0.068,0.048,0.137,0.093,0.209,0.137
            c0.085-0.094,0.16-0.198,0.223-0.308c-0.064-0.054-0.129-0.106-0.195-0.156c0.103-0.191,0.166-0.405,0.18-0.63
            C485.502,282.315,485.585,282.306,485.668,282.294z"/>
        <path fill="#F7941E" d="M470.455,285.037c0.626,0.619,1.636,0.612,2.254-0.016c0.618-0.626,0.611-1.636-0.016-2.254
            c-0.627-0.619-1.636-0.611-2.254,0.016C469.82,283.41,469.828,284.419,470.455,285.037z M470.67,284.871l0.21-0.189l0.411-0.368
            c0.079,0.054,0.169,0.083,0.261,0.087l0.108,0.541l0.055,0.277C471.346,285.259,470.962,285.144,470.67,284.871z M472.517,284.833
            c-0.163,0.166-0.359,0.279-0.567,0.34l-0.056-0.277l-0.108-0.541c0.052-0.024,0.101-0.058,0.144-0.102
            c0.032-0.032,0.058-0.066,0.08-0.104l0.53,0.15l0.272,0.076C472.749,284.542,472.651,284.698,472.517,284.833z M472.876,284.146
            l-0.272-0.077l-0.531-0.15c0.003-0.087-0.017-0.175-0.058-0.253l0.411-0.367l0.211-0.189
            C472.862,283.413,472.943,283.791,472.876,284.146z M472.479,282.934l-0.211,0.189l-0.411,0.367
            c-0.079-0.054-0.169-0.083-0.261-0.087l-0.108-0.54l-0.056-0.277C471.801,282.545,472.187,282.661,472.479,282.934z
             M470.63,282.972c0.164-0.166,0.36-0.279,0.568-0.34l0.056,0.278l0.108,0.54c-0.052,0.025-0.102,0.059-0.144,0.103
            c-0.032,0.032-0.058,0.067-0.079,0.104l-0.531-0.149l-0.272-0.077C470.399,283.263,470.498,283.107,470.63,282.972z
             M470.544,283.735l0.531,0.149c-0.004,0.087,0.016,0.175,0.058,0.253l-0.411,0.367l-0.211,0.189
            c-0.226-0.303-0.306-0.681-0.24-1.036L470.544,283.735z"/>
        <g>
            <polygon fill="#F6891F" points="478.018,284.56 476.845,284.802 477.083,285.171 476.168,285.76 476.628,286.473 477.542,285.884
                477.779,286.254 478.485,285.286 479.19,284.318 		"/>
                <rect x="475.573" y="286.097" transform="matrix(-0.5424 -0.8401 0.8401 -0.5424 493.5696 841.6016)" fill="#F6891F" width="0.812" height="0.578"/>
        </g>
        <g>
            <polygon fill="#AAD9B5" points="467.662,281.979 468.309,281.492 468.795,282.139 468.604,282.283 468.364,281.966
                468.267,282.655 468.028,282.621 468.125,281.932 467.808,282.171 		"/>
            <polygon fill="#AAD9B5" points="468.536,281.207 468.383,281.123 468.625,280.677 468.347,280.76 468.297,280.591
                468.862,280.423 469.03,280.989 468.862,281.039 468.779,280.761 		"/>
        </g>
        <polygon fill="#F26522" points="483.242,276.41 482.556,276.41 482.556,275.725 482.079,275.725 482.079,276.41 481.394,276.41
            481.394,276.887 482.079,276.887 482.079,277.572 482.556,277.572 482.556,276.887 483.242,276.887 	"/>
        <polygon fill="#F5EB00" points="464.282,281.729 463.552,281.729 463.552,280.999 463.044,280.999 463.044,281.729
            462.314,281.729 462.314,282.237 463.044,282.237 463.044,282.967 463.552,282.967 463.552,282.237 464.282,282.237 	"/>
        <polygon fill="#8DC63F" points="479.794,274.986 480.18,274.508 478.691,274.083 477.203,273.658 477.935,275.021 478.668,276.385
            479.052,275.907 480.133,276.776 480.875,275.856 	"/>
        <polygon fill="#4EA0B4" points="475.372,275.196 475.785,275.476 474.95,276.714 475.089,276.809 475.926,275.569 476.34,275.849
            476.381,275.267 476.422,274.684 	"/>
        <path fill="#00A0AF" d="M472.735,288.019c-0.315-0.315-0.827-0.315-1.143,0s-0.315,0.828,0,1.143c0.315,0.316,0.827,0.316,1.143,0
            C473.051,288.846,473.051,288.334,472.735,288.019z M472.376,288.803c-0.117,0.118-0.308,0.118-0.425,0s-0.117-0.308,0-0.426
            s0.308-0.118,0.425,0C472.495,288.495,472.495,288.685,472.376,288.803z"/>
        <polygon fill="#79AD36" points="466.727,291.092 466.916,290.521 468.627,291.087 468.691,290.894 466.98,290.327 467.169,289.755
            466.479,289.897 465.791,290.041 	"/>
        <polygon fill="#17AEB2" points="475.519,284.344 475.709,284.333 475.675,283.691 476.341,283.675 475.523,282.334 474.77,283.713
            475.484,283.695 	"/>
        <g>
            <path fill="#BFE2CE" d="M467.377,278.755l-0.935-0.155l0.155-0.935c0.016-0.093-0.047-0.182-0.142-0.198s-0.182,0.047-0.198,0.142
                l-0.156,0.934l-0.935-0.155c-0.093-0.016-0.183,0.048-0.197,0.141c-0.016,0.094,0.047,0.183,0.141,0.199l0.935,0.155l-0.156,0.935
                c-0.016,0.094,0.048,0.183,0.142,0.198c0.094,0.016,0.182-0.048,0.198-0.142l0.155-0.935l0.934,0.155
                c0.094,0.016,0.183-0.047,0.198-0.141C467.534,278.859,467.471,278.771,467.377,278.755z"/>
            <path fill="#95A43A" d="M468.265,278.739c0.116-0.018,0.232-0.04,0.349-0.064c-0.005-0.187-0.033-0.373-0.082-0.553
                c-0.118,0.008-0.236,0.021-0.353,0.037c-0.065-0.216-0.166-0.42-0.301-0.606c0.084-0.083,0.166-0.169,0.245-0.257
                c-0.114-0.148-0.247-0.283-0.392-0.4c-0.091,0.077-0.179,0.157-0.265,0.238c-0.186-0.142-0.388-0.245-0.599-0.313
                c0.019-0.116,0.034-0.234,0.047-0.352c-0.18-0.053-0.366-0.084-0.553-0.093c-0.028,0.115-0.053,0.231-0.074,0.348
                c-0.227-0.004-0.453,0.029-0.669,0.099c-0.053-0.105-0.109-0.209-0.169-0.312c-0.177,0.062-0.345,0.146-0.502,0.249
                c0.046,0.109,0.094,0.218,0.145,0.325c-0.167,0.116-0.318,0.259-0.449,0.428c-0.011,0.015-0.022,0.03-0.034,0.046
                c-0.105-0.055-0.211-0.105-0.32-0.153c-0.105,0.154-0.194,0.321-0.26,0.496c0.101,0.063,0.204,0.122,0.307,0.178
                c-0.076,0.217-0.112,0.442-0.112,0.666c-0.116,0.019-0.232,0.041-0.348,0.066c0.004,0.187,0.032,0.373,0.081,0.553
                c0.118-0.009,0.236-0.021,0.353-0.037c0.064,0.215,0.166,0.42,0.3,0.606c-0.083,0.083-0.165,0.169-0.244,0.257
                c0.114,0.148,0.247,0.283,0.392,0.4c0.09-0.078,0.178-0.156,0.264-0.238c0.186,0.141,0.388,0.246,0.599,0.313
                c-0.018,0.117-0.033,0.234-0.045,0.352c0.179,0.053,0.365,0.084,0.552,0.093c0.028-0.115,0.053-0.231,0.074-0.347
                c0.227,0.004,0.453-0.029,0.669-0.099c0.053,0.105,0.109,0.209,0.169,0.313c0.176-0.063,0.345-0.146,0.502-0.249
                c-0.045-0.11-0.093-0.218-0.145-0.325c0.167-0.115,0.318-0.258,0.449-0.428c0.012-0.015,0.023-0.03,0.034-0.045
                c0.105,0.053,0.212,0.105,0.321,0.154c0.105-0.154,0.193-0.321,0.259-0.497c-0.101-0.062-0.204-0.121-0.308-0.178
                C468.228,279.189,468.265,278.965,468.265,278.739z"/>
            <g>
                <path fill="#A9BF38" d="M466.039,279.978c0.683,0.114,1.329-0.348,1.442-1.03c0.114-0.683-0.348-1.329-1.031-1.442
                    c-0.683-0.113-1.329,0.348-1.441,1.031C464.894,279.219,465.356,279.864,466.039,279.978z M466.396,277.831
                    c0.503,0.083,0.843,0.56,0.759,1.062c-0.084,0.503-0.559,0.843-1.062,0.759c-0.503-0.083-0.842-0.559-0.759-1.062
                    C465.417,278.087,465.894,277.747,466.396,277.831z"/>
                <circle fill="#A9BF38" cx="466.244" cy="278.741" r="0.467"/>
            </g>
        </g>
        <g>
            <path fill="#BFB131" d="M484.648,277.401c-0.708-0.004-1.284,0.567-1.287,1.274c-0.003,0.708,0.568,1.283,1.275,1.287
                c0.708,0.003,1.284-0.567,1.287-1.275C485.926,277.98,485.356,277.405,484.648,277.401z M484.638,279.625
                c-0.521-0.002-0.941-0.427-0.939-0.947c0.002-0.521,0.427-0.941,0.948-0.939c0.521,0.002,0.94,0.427,0.938,0.947
                C485.583,279.208,485.159,279.627,484.638,279.625z"/>
            <circle fill="#CFB52B" cx="484.642" cy="278.682" r="0.477"/>
        </g>
        <circle fill="#8DC63F" cx="465.021" cy="284.692" r="0.654"/>
        <g>
            <path fill="#FEBC11" d="M462.138,287.298c-0.105-0.073-0.167-0.166-0.184-0.277c-0.018-0.112,0.012-0.224,0.091-0.336
                c0.085-0.123,0.188-0.197,0.307-0.222c0.117-0.023,0.234,0.003,0.349,0.083c0.109,0.076,0.171,0.168,0.188,0.276
                s-0.017,0.221-0.1,0.339c-0.084,0.122-0.186,0.195-0.304,0.218C462.367,287.405,462.251,287.377,462.138,287.298z
                 M463.739,287.302l-1.993,0.806l-0.251-0.174l1.99-0.808L463.739,287.302z M462.568,286.723c-0.101-0.069-0.199-0.035-0.296,0.104
                c-0.092,0.132-0.09,0.231,0.006,0.297c0.097,0.069,0.194,0.034,0.289-0.104C462.66,286.888,462.66,286.788,462.568,286.723z
                 M462.535,288.688c-0.104-0.073-0.166-0.165-0.183-0.276c-0.019-0.111,0.012-0.224,0.09-0.336
                c0.085-0.123,0.188-0.197,0.306-0.221c0.119-0.025,0.235,0.003,0.349,0.083c0.11,0.076,0.173,0.168,0.189,0.275
                c0.017,0.107-0.017,0.22-0.1,0.338c-0.085,0.121-0.187,0.194-0.305,0.219C462.765,288.795,462.648,288.768,462.535,288.688z
                 M462.962,288.112c-0.1-0.069-0.197-0.035-0.295,0.104c-0.092,0.132-0.09,0.231,0.007,0.299c0.097,0.068,0.194,0.033,0.29-0.104
                c0.044-0.064,0.066-0.123,0.065-0.176C463.027,288.184,463.005,288.142,462.962,288.112z"/>
        </g>
        <g>
            <path fill="#95A43A" d="M482.164,285.112l0.14,0.213l-0.144,0.094l-0.136-0.207c-0.134,0.087-0.277,0.138-0.431,0.154
                l-0.179-0.272c0.058,0.006,0.134-0.003,0.227-0.026c0.093-0.023,0.17-0.052,0.231-0.087l-0.235-0.357
                c-0.191,0.044-0.335,0.051-0.436,0.021c-0.099-0.03-0.18-0.093-0.244-0.188c-0.067-0.103-0.089-0.214-0.063-0.334
                c0.025-0.12,0.094-0.226,0.206-0.316l-0.12-0.183l0.143-0.094l0.118,0.179c0.143-0.084,0.26-0.13,0.354-0.137l0.174,0.266
                c-0.128,0.003-0.254,0.038-0.376,0.101l0.245,0.372c0.177-0.043,0.319-0.051,0.425-0.025c0.106,0.025,0.19,0.085,0.252,0.18
                c0.072,0.108,0.095,0.219,0.069,0.331C482.355,284.907,482.283,285.013,482.164,285.112z M481.455,284.347l-0.205-0.312
                c-0.08,0.077-0.094,0.154-0.042,0.232C481.251,284.335,481.334,284.362,481.455,284.347z M481.816,284.584l0.196,0.297
                c0.084-0.076,0.1-0.153,0.047-0.232C482.018,284.585,481.937,284.563,481.816,284.584z"/>
        </g>
        <g>
            <path fill="#FEBC11" d="M479.64,280.716l-0.314-0.131l-0.234,0.557l-0.527-0.221l0.233-0.557l-1.142-0.479l0.154-0.368
                l1.812-1.236l0.569,0.238l-0.692,1.653l0.315,0.132L479.64,280.716z M479.429,278.88l-0.011-0.005
                c-0.035,0.037-0.097,0.091-0.185,0.163l-0.927,0.636l0.665,0.278l0.346-0.825C479.348,279.055,479.385,278.972,479.429,278.88z"/>
        </g>
    </g>';
	}
	/**
	 * @param string $cmd
	 * @return string
	 * @throws FileNotFoundException
	 */
	public static function execute(string $cmd): string {
		$executable = './phantomjs';
		$packagePath = self::getExportPackagePath();
		if(AppMode::isWindows()){
			$executable = 'phantomjs.exe';
			S3Public::downloadIfNotExists('bin/phantomjs.exe', "$packagePath/$executable");
		}
		$cmd = "cd " . $packagePath . " && $executable " .
			$cmd;                           // Seems to have problems when putting variables within quotes sometimes
		$output = ThisComputer::exec($cmd);    // Not sure what output ": not found" means but it seems to work anyway
		return $output;
	}
	/**
	 * @return mixed
	 */
	public function getImageType(): string{
		return $this->imageType ?? self::DEFAULT_IMAGE_FORMAT;
	}
	/**
	 * @param mixed $imageType
	 * @return HighchartExport
	 */
	public function setImageType($imageType): HighchartExport{
		$this->imageType = $imageType;
		return $this;
	}
	/**
	 * @param float $scale
	 * @return HighchartExport Set the zoomFactor of the page rendered by PhantomJs. For example, if the chart.width
	 *     option in the Set the zoomFactor of the page rendered by PhantomJs. For example, if the chart.width option
	 *     in the chart configuration is set to 600 and the scale is set to 2, the output raster image will have a
	 *     pixel width of 1200. So this is a convenient way of increasing the resolution without decreasing the font
	 *     size and line widths in the chart. This is ignored if the -width parameter is set.
	 */
	public function setScale(float $scale): HighchartExport{
		$this->scale = $scale;
		return $this;
	}
	/**
	 * @param float $width
	 * @return HighchartExport Set the exact pixel width of the exported image or pdf. This overrides the -scale
	 *     parameter. Set the exact pixel width of the exported image or pdf. This overrides the -scale parameter.
	 */
	public function setWidth(float $width): HighchartExport{
		$this->width = $width;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getOutputFilePath(): string{
		$name = $this->getOutputFileName();
		if(!$this->outputPath){
			return $this->outputPath = self::getOutputFolder() . "/$name";
		}
		return $this->outputPath;
	}
	public static function getOutputFolder(): string{
		return self::getExportPackagePath() . "/output";
	}
	/**
	 * @return string
	 */
	public function getConstructor(): string{
		$config = $this->getExportableConfig();
		$useHighStock = $config->useHighStocks ?? $this->useHighStock;
		if($useHighStock){
			$constr = "StockChart";
		} else{
			$constr = "Chart";
		}
		return $constr;
	}
	private static function getExportPackagePath(): string{
		return FileHelper::absPath('vendor/mikepsinn/php-highcharts-exporter');
	}
	private function writeConfig(){
		$configPath = self::getConfigPath();
		$config = $this->getExportableConfig();
		if(isset($config->navigator)){
			$config->navigator->enabled = false;
		}
		if(isset($config->rangeSelector)){
			$config->rangeSelector->enabled = false;
		}
		if($config->useHighStocks){
			le("should not useHighStocks on exports!", $config);
		}
		if(!$config->chart){
			le("No chart!", $config);
		}
		\App\Logging\ConsoleLog::info("Highchart config at $configPath");
		if(!$config->series){
			le("No series on: ", $config);
		}
		$json = json_encode($config, JSON_PRETTY_PRINT);
		if(!str_contains($json, '"chart"')){
			$json = json_encode($config, JSON_PRETTY_PRINT);
			le("no chart after json_encode on " . $json, $config);
		}
		FileHelper::writeByFilePath($configPath, $json);
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getConfigPath(string $name = "config"): string{
		return storage_path("charts/$name.json");
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getConfigContents(string $name = "config"): string{
		return json_decode(file_get_contents(self::getConfigPath($name)));
	}
	/**
	 * @param $fileName
	 */
	public static function deleteOutputImageFile($fileName){
		$filePath = self::getOutputFolder() . "/$fileName";
		FileHelper::delete($filePath);
	}
	/**
	 * @param string $name
	 */
	public static function deleteConfig(string $name = "config"){
		$filePath = self::getConfigPath($name);
		try {
			unlink($filePath);
		} catch (\Throwable $e) {
		}
	}
	/**
	 * @return string
	 */
	private function getScaleWidthFlags(): string{
		$flags = "";
		if($this->scale){
			$flags .= " -scale $this->scale ";
		}
		if($this->width){
			$flags .= " -width $this->width ";
		}
		return $flags;
	}
	/**
	 * @return string
	 */
	private function getOutputFileName(): string{
		$type = $this->getImageType();
		if(!$this->chart){
			return $this->highchartConfig->getId() . ".$type";
		}
		$c = $this->getChart();
		// Make sure to use unique filename or we have conflicts if multiple exports occur simultaneously
		return $c->getFileName($type);
	}
	/**
	 * @param string $type
	 * @param string $data
	 * @param string $alt
	 * @param string $title
	 * @param string $elementId
	 * @return string
	 */
	public static function imageDataToHtml(string $type, string $data, string $alt, string $title,
		string $elementId): string{
		if($type === self::SVG){
			$imageHtml = $data;
		} else{
			// max-width: unset; width: unset; required to avoid blur from WP css
			$style = "max-width: 100%; width: unset; margin: auto;"; // DO NOT REMOVE THIS!!!
			$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
			$imageHtml = '
                <img id="' . $elementId . '-image"
                    class="chart-img"
                    style="' . $style . '"
                    src="' . $base64 . '"
                    alt="' . $alt . '"
                    title="' . $title . '"/>';
		}
		if(stripos($imageHtml, "position: absolute") !== false){
			throw new \LogicException("Do not use position: absolute because all the images will pile on top " .
				"of each other!");
		}
		return $imageHtml;
	}
	/**
	 * @param string $alt
	 * @param string $title
	 * @param string $elementId
	 * @return string
	 */
	public function getHtml(string $alt = "Chart", string $title = "Chart", string $elementId = "chart"): string{
		$data = $this->getImageData();
		return self::imageDataToHtml($this->getImageType(), $data, $alt, $title, $elementId);
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if($this->chart){
			return $this->getChart()->getTitleAttribute();
		}
		return $this->getHighchartConfig()->title->text;
	}
	/**
	 * @return string
	 */
	private function getSubtitle(): string{
		if($this->chart){
			return $this->getChart()->getSubtitleAttribute();
		}
		return $this->getHighchartConfig()->subtitle->text;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getTitleAttribute() . " " . (new \ReflectionClass(static::class))->getShortName();
	}
}
