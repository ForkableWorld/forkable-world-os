<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\Computers\ThisComputer;
use App\DataSources\Connectors\Exceptions\RecentImportException;
use App\DataSources\Connectors\TigerViewConnector;
use App\Exceptions\TooManyMeasurementsException;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\User\QMUser;
use App\Types\QMStr;
use App\UI\ImageHelper;
use App\UI\QMColor;
use App\Variables\CommonVariables\GoalsCommonVariables\DailyAverageGradeCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class TigerViewConnectTest extends SlimStagingTestCase {
	public const DISABLED_UNTIL      = TigerViewConnector::TESTING_DISABLED_UNTIL;
	public const REASON_FOR_SKIPPING = "School is out";
	protected $retry = true;
	public function testTigerViewConnectImportEmail(): void{
		$this->skipTest("TODO: Fix this test");
		//TigerViewConnector::addConnectorIdToMeasurements();
		if(!TigerViewConnector::ENABLED){
			$this->skipTest("Tigerview disabled for summer");
			return;
		}
		if($this->weShouldSkip()){
			return;
		}
		$userId = 1;
		$this->checkDailyAverageMeasurements($userId);
		$avg = QMUserVariable::getByNameOrId($userId, DailyAverageGradeCommonVariable::NAME);
		$avgByDate = $avg->getValidDailyMeasurementsWithTagsAndFilling();
		$this->assertEquals(200, $avg->maximumAllowedValueInUserUnit);
		$connector = TigerViewConnector::getByUserId($userId);
		$u = QMUser::find($userId);
		/** @var Connection $connection */
		$connection = $connector->getConnectionIfExists();
		$at = $connector->calculateLatestMeasurementAt();
		$fromAt = $connector->getFromAt();
		$latestAssignmentMeasurementAt = $connector->getLatestAssignmentMeasurementAt();
		$this->assertEquals($fromAt, $latestAssignmentMeasurementAt);
		try {
			$connection->import(__METHOD__);
		} catch (RecentImportException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$connection->logLinkToHistory();
			$connection->logMeasurementsTable();
		} catch (TooManyMeasurementsException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		$spanish = QMUserVariable::getByNameOrId($userId, "Spanish Class Daily Average Grade");
		$lastSpanish = $spanish->getLastDailyMeasurementWithTagsAndFilling();
		$spanishByDate = $spanish->getValidDailyMeasurementsWithTagsAndFilling();
		$lastAvg = $avg->getLastDailyMeasurementWithTagsAndFilling();
		lei($lastSpanish->getStartAt() > $lastAvg->getStartAt(),
			"$lastSpanish->startTimeString > $lastAvg->startTimeString");
		$face = ImageHelper::gradeToFace(0.0);
		$this->assertContains('depressed', $face);
		$color = QMColor::gradeToColor(0.0);
		$this->assertContains(QMColor::HEX_DARK_GRAY, $color);
		$this->checkDailyAverageMeasurements($userId);
		$lines = QMStr::getNotEmptyLinesAsArray(self::TEST_CELL_HTML);
		$assignmentGrades = TigerViewConnector::linesToAssignmentGradeArray($lines, self::TEST_CELL_HTML);
		$this->checkDailyAverageMeasurements($userId);
	}
	/**
	 * @param int $userId
	 */
	protected function checkDailyAverageMeasurements(int $userId): void{
		$v = DailyAverageGradeCommonVariable::getUserVariableByUserId($userId);
		$measurements = $v->getValidDailyMeasurementsWithTagsAndFilling();
		$measurements = QMMeasurement::indexMeasurementsByStartAt($measurements);
		foreach($measurements as $dailyMeasurement){
			$groupedMeasurements = $dailyMeasurement->getGroupedMeasurements();
			if(strtotime($dailyMeasurement->getDate()) === strtotime("2019-11-04")){
				$this->assertCountAndLog(3, $groupedMeasurements);
			}
			if(strtotime($dailyMeasurement->getDate()) === strtotime("2019-11-05")){
				$this->assertCountAndLog(5, $groupedMeasurements);
			}
		}
	}
	public $expectedResponseSizes = [
		'success' => 0.004,
		'status' => 0.012,
	];
	public $slimEnvironmentSettings = [
		'REQUEST_METHOD' => 'GET',
		'REMOTE_ADDR' => '192.168.10.1',
		'SCRIPT_NAME' => '',
		'PATH_INFO' => '/api/v3/connectors/tigerview/connect',
		'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
		'SERVER_PORT' => '443',
		'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
		'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
		'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
		'HTTP_SEC_FETCH_SITE' => 'same-site',
		'HTTP_SEC_FETCH_MODE' => 'cors',
		'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
		'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
		'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
		'HTTP_ACCEPT' => 'application/json, text/plain, */*',
		'HTTP_CONNECTION' => 'keep-alive',
		'CONTENT_LENGTH' => '',
		'CONTENT_TYPE' => '',
		'slim.url_scheme' => 'https',
		'slim.input' => '',
		'slim.request.query_hash' => [
			'appName' => 'QuantiModo',
			'appVersion' => '2.9.1022',
			'accessToken' => 'mike-test-token',
			'clientId' => 'quantimodo',
			'platform' => 'web',
			'XDEBUG_SESSION_START' => 'PHPSTORM',
			'username' => 'M.Sinn',
			'password' => 'tiger1955',
		],
		'slim.request.form_hash' => [],
		'responseStatusCode' => 200,
		'unixtime' => 1572987066,
		'requestDuration' => 72.6425199508667,
	];
	private const TEST_CELL_HTML = 'Interactivity Biodiversity in Amazon


                                                        Z





                                                Interactivity Ecosystem Services


                                                        15/15';
	private const TEST_HTML      = '

<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />






        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <title>Home View Summary</title>


        <!-- 3rd party CSS our globals override -->
        <link href="/HomeAccess/Stylesheets/Themes/custom-theme/jquery?v=khU3GP_ixo8tMiLwXJEqkRywEE1c7SBpfpbj4KSDyXA1" rel="Stylesheet" type="text/css" />

        <link href="/HomeAccess/Stylesheets/Trirand/ui.jqgrid.css" rel="Stylesheet" type="text/css" />

        <!-- Our globals -->
        <link href="/HomeAccess/eSchoolPLUSResource/GetEmbeddedStyle?file=CrossSite" rel="Stylesheet" type="text/css" />
        <link href="/HomeAccess/Stylesheets/Site.css" rel="Stylesheet" type="text/css" />
        <link rel="SHORTCUT ICON" href="/HomeAccess/Media/Themes/Base/Common/favicon.ico"/>

        <!-- Our shared -->
        <link href="/HomeAccess/eSchoolPLUSResource/GetEmbeddedStyle?file=ComboBox" rel="Stylesheet" type="text/css"  />
        <link href="/HomeAccess/Stylesheets/Common/Navigation.css" rel="Stylesheet" type="text/css" />
        <link href="/HomeAccess/Stylesheets/Common/font-awesome.css" rel="Stylesheet" type="text/css" />

        <!-- JS -->

    <link href="/HomeAccess/Stylesheets/Frame/Frame?v=xitnFO3FS2JzpghX9Dgraly36VeDtq1Ukx66KAYKqMQ1" rel="stylesheet"/>

<link href="/HomeAccess/eSchoolPLUSResource/GetEmbeddedStyle?file=SessionTracking" rel="stylesheet" />





        <script src="/HomeAccess/Scripts/libs/modernizr-2.8.3.js" type="text/javascript"></script>
        <script src="/HomeAccess/Scripts/Common/BrowserTest.js" type="text/javascript"></script>
        <script type="text/javascript">
            if (!BrowserTest()) {
                document.location = \'/HomeAccess/\' + \'BrowserReject\'; // Done because browsers that vail won\'t support jQuery
            }
        </script>

        <script src="/HomeAccess/Scripts/libs/jquery?v=kFkgd6hNofX_b_JGNSXAqVYLW6XzxJEd8j1YdcwKx3I1" type="text/javascript"></script>

        <script src="/HomeAccess/eSchoolPLUSResource/GetEmbeddedScript?file=SungardCommon" type="text/javascript"></script>
        <script src="/HomeAccess/eSchoolPLUSResource/GetEmbeddedScript?file=Combobox" type="text/javascript"></script>

        <script src="/HomeAccess/Scripts/Trirand/ui.multiselect.js" type="text/javascript" ></script>
        <script src="/HomeAccess/Scripts/Trirand/i18n/grid.locale-en.js" type="text/javascript"></script>
        <script src="/HomeAccess/Scripts/Trirand/jquery.jqGrid.js" type="text/javascript"></script>
        <script src="/HomeAccess/Scripts/Common/HAC.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(function () {
                parseForComboBoxes();

                SunGard.Hac.Area(\'\');
                SunGard.Hac.Controller(\'Home\');
                SunGard.Hac.Action(\'WeekView\');

                SunGard.Common.Init(\'/HomeAccess/\');
                SunGard.Hac.Init(\'/HomeAccess/\');
            });
        </script>


        <!-- This should be at the bottom of this section -->

    <script src="/HomeAccess/eSchoolPLUSResource/GetEmbeddedScript?file=SungardCommon" type="text/javascript"></script>
    <script src="/HomeAccess/Scripts/Frame/Frame?v=D7AjL3NIcSxfWSRGw_1OFC4HrSI6G9N1SvCGBVjhMME1"></script>

    <script src="/HomeAccess/eSchoolPLUSResource/GetEmbeddedScript?file=SessionTracking" type="text/javascript"></script>

    <script>
        $(function() {
            var timeout = 1200;
            var expireAfter = 1197.6870369;
            var showBefore = 120;
            var redirectUrl = "/HomeAccess/Account/TimedOut";
            var settingsOverride = {
                captionText: null,
                renewText: null,
                ignoreText: null
            };

            SunGard.Common.SessionTracking.Init(timeout, expireAfter, showBefore, redirectUrl, settingsOverride);
        });
    </script>

    <style>
        .sg-asp-table th,
        .sg-asp-table-header-row,
        .sg-banner-logo-color,
        .sg-hac-submenu,
        .ui-jqgrid-view .ui-jqgrid-hdiv,
        .ui-jqgrid tr.ui-jqgrid-labels th  { /* asp datatables dont produce th elements, only td. Use this class to index the header rows */
            background-color: #006699;
            color: #FFFFFF
        }
        .sg-asp-table-header-row a,
        .sg-banner-text-color {
            color: #FFFFFF
        }
    </style>

    <!-- This should be at the bottom of this section -->

    <script>
        $(SunGard.Hac.Frame.Init);
        $(function() {
            var helpUrl = \'/HomeAccess/HelpTAC/Default.htm\';
            SunGard.Hac.Frame.Banner.Init();
        });
    </script>

    <!-- This should be at the bottom of this section -->

    <script src="/HomeAccess/Scripts/libs/bootstrap-popover.js"></script>

    <script src="/HomeAccess/Scripts/SunGard/SunGard.Common.Html.js"></script>

    <script src="/HomeAccess/Scripts/Common/SunGard.Hac.SharedClasswork.js"></script>

    <script src="/HomeAccess/Scripts/Home/Home?v=WhVj7eFfJB5W0cG6mMo7GbG_J-iCS53sPlpwOtoR8hU1"></script>


    <script type="text/javascript">
        $(function () {
            SunGard.Hac.Home.Init();
        });
    </script>
    <link href="/HomeAccess/Stylesheets/Common/bootstrap-popover.css" rel="Stylesheet" type="text/css" />
    <link href="/HomeAccess/Stylesheets/Home/HomeStyle.css" rel="Stylesheet" type="text/css" />



    </head>
    <!-- add webkit-overflow to fix scrolling on IOS Devices-->
    <body style="-webkit-overflow-scrolling: touch;">







    <div class="sg-main-header">



<div class="sg-banner sg-banner-default-logo" data-contact-id="40055" data-student-id="24337">



        <div class="sg-banner-left sg-banner-left-default-logo">
            <div class="sg-banner-info-container">
                <span class="sg-banner-text sg-banner-text-color">ECUSD7</span>
                <span class="sg-banner-text sg-banner-text-color">Home Access Center</span>
            </div>
        </div>

    <div class="sg-banner-right sg-banner-right-default-logo">
        <div class="sg-banner-menu-container">
            <div class="sg-banner-menu-left"></div>
            <ul class="sg-banner-menu">
                <li class="sg-banner-menu-element sg-menu-element-identity">
                    <span>Michael Sinn</span>
                    <ul class="sg-banner-submenu">
                            <li><a href="/HomeAccess/Account/Alerts">My Alerts</a></li>
                        <li><a href="/HomeAccess/Account/Details">My Account</a></li>
                    </ul>
                </li>


                <li class="sg-banner-menu-divider"></li>
                <li class="sg-banner-menu-element sg-menu-element-logoff"><a href="/HomeAccess/Account/LogOff">Logoff</a></li>
            </ul>
            <div class="sg-banner-menu-right"></div>
        </div>
            <div class="sg-banner-chooser">
                <span class="sg-banner-text sg-banner-text-color">Ivy Sinn</span>

            </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        SunGard.Common.AddResource(\'Cancel\', \'Cancel\');
        SunGard.Common.AddResource(\'Submit\', \'Submit\');
        SunGard.Common.AddResource(\'ChangeStudentTitle\', \'Choose Your Student\');
    })
</script>


<script>
        $(function() {
            $(\'#hac-Home\').click(function() { window.location = \'/HomeAccess/Home/Home\'; });
        });
            $(function () {
                $(\'#hac-nav-submenu-Home-WeekView\').click(function () { window.location = \'/HomeAccess/Home/WeekView\'; });
            });
            $(function () {
                $(\'#hac-nav-submenu-Home-Calendar\').click(function () { window.location = \'/HomeAccess/Home/Calendar\'; });
            });
            $(function () {
                $(\'#hac-nav-submenu-Home-SchoolLinks\').click(function () { window.location = \'/HomeAccess/Home/SchoolLinks\'; });
            });
        $(function() {
            $(\'#hac-Attendance\').click(function() { window.location = \'/HomeAccess/Attendance\'; });
        });
            $(function () {
                $(\'#hac-nav-submenu-Attendance-MonthView\').click(function () { window.location = \'/HomeAccess/Attendance/MonthView\'; });
            });
        $(function() {
            $(\'#hac-Classes\').click(function() { window.location = \'/HomeAccess/Classes\'; });
        });
            $(function () {
                $(\'#hac-nav-submenu-Classes-Classwork\').click(function () { window.location = \'/HomeAccess/Classes/Classwork\'; });
            });
            $(function () {
                $(\'#hac-nav-submenu-Classes-Schedule\').click(function () { window.location = \'/HomeAccess/Classes/Schedule\'; });
            });
        $(function() {
            $(\'#hac-Grades\').click(function() { window.location = \'/HomeAccess/Grades\'; });
        });
            $(function () {
                $(\'#hac-nav-submenu-Grades-ReportCard\').click(function () { window.location = \'/HomeAccess/Grades/ReportCard\'; });
            });
        $(function() {
            $(\'#hac-Registration\').click(function() { window.location = \'/HomeAccess/Registration\'; });
        });
            $(function () {
                $(\'#hac-nav-submenu-Registration-Demographic\').click(function () { window.location = \'/HomeAccess/Registration/Demographic\'; });
            });
            $(function () {
                $(\'#hac-nav-submenu-Registration-Fees\').click(function () { window.location = \'/HomeAccess/Registration/Fees\'; });
            });
</script>

<div id="hac-nav-menu" class ="sg-hac-menu">
    <div class="sg-hac-menu-options">
            <div id="hac-Home" class="sg-hac-menu-option sg-hac-menu-option-selected" subDiv="#hac-nav-submenu-Home-options">
                <img alt="Home" src="/HomeAccess/Media/images/Menu/hac-Home.png" />
                <span>Home</span>
            </div>
            <div id="hac-Attendance" class="sg-hac-menu-option" subDiv="#hac-nav-submenu-Attendance-options">
                <img alt="Attendance" src="/HomeAccess/Media/images/Menu/hac-Attendance.png" />
                <span>Attendance</span>
            </div>
            <div id="hac-Classes" class="sg-hac-menu-option" subDiv="#hac-nav-submenu-Classes-options">
                <img alt="Classes" src="/HomeAccess/Media/images/Menu/hac-Classes.png" />
                <span>Classes</span>
            </div>
            <div id="hac-Grades" class="sg-hac-menu-option" subDiv="#hac-nav-submenu-Grades-options">
                <img alt="Grades" src="/HomeAccess/Media/images/Menu/hac-Grades.png" />
                <span>Grades</span>
            </div>
            <div id="hac-Registration" class="sg-hac-menu-option" subDiv="#hac-nav-submenu-Registration-options">
                <img alt="Registration" src="/HomeAccess/Media/images/Menu/hac-Registration.png" />
                <span>Registration</span>
            </div>
    </div>
</div>
<div id="hac-nav-submenu" class="sg-hac-submenu">
    <div id="hac-nav-submenu-Home-options" class="sg-hac-submenu-options" style="">
            <div id="hac-nav-submenu-Home-WeekView" class="sg-hac-submenu-option sg-hac-submenu-option-selected">
                <span>Week View</span>
            </div>
            <div id="hac-nav-submenu-Home-Calendar" class="sg-hac-submenu-option">
                <span>Calendar</span>
            </div>
            <div id="hac-nav-submenu-Home-SchoolLinks" class="sg-hac-submenu-option">
                <span>School Links</span>
            </div>
    </div>
    <div id="hac-nav-submenu-Attendance-options" class="sg-hac-submenu-options" style="display:none;">
            <div id="hac-nav-submenu-Attendance-MonthView" class="sg-hac-submenu-option">
                <span>Month View</span>
            </div>
    </div>
    <div id="hac-nav-submenu-Classes-options" class="sg-hac-submenu-options" style="display:none;">
            <div id="hac-nav-submenu-Classes-Classwork" class="sg-hac-submenu-option">
                <span>Classwork</span>
            </div>
            <div id="hac-nav-submenu-Classes-Schedule" class="sg-hac-submenu-option">
                <span>Schedule</span>
            </div>
    </div>
    <div id="hac-nav-submenu-Grades-options" class="sg-hac-submenu-options" style="display:none;">
            <div id="hac-nav-submenu-Grades-ReportCard" class="sg-hac-submenu-option">
                <span>Report Card</span>
            </div>
    </div>
    <div id="hac-nav-submenu-Registration-options" class="sg-hac-submenu-options" style="display:none;">
            <div id="hac-nav-submenu-Registration-Demographic" class="sg-hac-submenu-option">
                <span>Demographic</span>
            </div>
            <div id="hac-nav-submenu-Registration-Fees" class="sg-hac-submenu-option">
                <span>Fees</span>
            </div>
    </div>
</div>
    </div>




<div id="MainContent" class="sg-main-content">


    <div style="display: none;" id="session-block">
    </div>
    <div id="session-container" style="display: none;" class="sg-container" id="TimedOutSectionContainer">
        <div class="sg-header">
		    <img class="sg-header-image" src="/HomeAccess/Media/Themes/Base/Headers/header-session-expire.png" alt="Session Timed Out" />
		    <span class="sg-header-heading">Session Warning</span>
        </div>
        <div class="sg-container-content" id="session-background">
            <p>Your session is about to expire.  Unsaved changes will be lost.</p>
            <div id="session-buttons" class="sg-container-button">
                <button Name="session-renew" Value="" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="session-renew"><span class="ui-button-text">Keep session open</span></button>
                <button Name="session-ignore" Value="" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="session-ignore"><span class="ui-button-text">Ignore</span></button>
            </div>
        </div>
    </div>













<form action="/HomeAccess/Home/WeekView?startDate=2019-11-04%2000%3A00%3A00" method="post"><div id="mainpage" class=\'sg-hac-content\'>
    <div class="sg-header">
                <label class="sg-header-heading" id="lblTitle">Today&#39;s News</label>
            </div>
            <div class="sg-content-grid">
            <ul>
                        <li>There is no current news.</li>

            </ul>

            </div>

    <div class="sg-header">
                    <span class="sg-red-back sg-fee-balance sg-right" >
                 $90.00
            </span>
            <span class="sg-red-front sg-fee-balance sg-right sg-fee-pointer" >
                 <a title=\'View Fee Balance\' onclick="location.href=\'/HomeAccess/Registration/Fees\'; return false;"> Fee Balance</a>
            </span>
    </div>
    <div class="sg-content-grid">
        <div class="sg-home-sub-header sg-clearfix">
            <div class="sg-left">
                <div class="sg-buttonset sg-home-nav-button">


                    <button title=\'View previous weeks\' class="sg-home-sub-header-button" onclick="location.href=\'/HomeAccess/Home/WeekView?startDate=10%2F28%2F2019%2000%3A00%3A00\'; return false;" ><i class="icon-chevron-left"></i></button>


                    <button title=\'View future weeks\' class="sg-home-sub-header-button" onclick="location.href=\'/HomeAccess/Home/WeekView?startDate=11%2F11%2F2019%2000%3A00%3A00\'; return false;" ><i class="icon-chevron-right"></i></button>
                </div>
                <label class="sg-5px-margin sg-home-nav-label">Monday November 04, 2019 - Friday November 08, 2019 </label>
            </div>
            <div class="sg-right">
                    <button title=\'Return to today&#39;s date\' class="sg-button sg-home-sub-header-button  sg-page-button-font" onclick="location.href=\'/HomeAccess/Home/WeekView\'; return false;"> Today </button>
                                    <button title=\'View student&#39;s schedule\' class="sg-button sg-home-sub-header-button sg-page-button-font" onclick="location.href=\'/HomeAccess/Classes/Schedule\'; return false;" >  View Full Schedule </button>
            </div>
        </div>
        <table class="sg-asp-table sg-table-horizontal-border sg-homeview-table">
            <thead>
                <tr class=\'sg-asp-table-header-row\'>
                    <td ><div class="sg-asp-table-header-cell-large-text">Class</div></td>
                        <td ><div class="sg-asp-table-header-cell-large-text">Current Average</div></td>
                                            <td class="sg-cell-width">
                            <div>
                                    <a title= \'Schedule for Monday 11/04\' href="javascript:SunGard.Hac.Home.ViewSchedulePopUp(\'24337     \', \'11/04/2019\', \'/HomeAccess/Content/Student/DailySchedule.aspx\');" >

                                Monday

                                    </span>
                            </div>
                            11/04
                            <div class="sg-right">Day: M                            </div>
                        </td>
                        <td class="sg-cell-width">
                            <div>
                                    <a title= \'Schedule for Tuesday 11/05\' href="javascript:SunGard.Hac.Home.ViewSchedulePopUp(\'24337     \', \'11/05/2019\', \'/HomeAccess/Content/Student/DailySchedule.aspx\');" >

                                Tuesday

                                    </span>
                            </div>
                            11/05
                            <div class="sg-right">Day: T                            </div>
                        </td>
                        <td class="sg-cell-width">
                            <div>
                                    <a title= \'Schedule for Wednesday 11/06\' href="javascript:SunGard.Hac.Home.ViewSchedulePopUp(\'24337     \', \'11/06/2019\', \'/HomeAccess/Content/Student/DailySchedule.aspx\');" >

                                Wednesday

                                    </span>
                            </div>
                            11/06
                            <div class="sg-right">Day: W                            </div>
                        </td>
                        <td class="sg-asp-table-header-selected sg-cell-width">
                            <div>
                                    <a title= \'Schedule for Thursday 11/07\' href="javascript:SunGard.Hac.Home.ViewSchedulePopUp(\'24337     \', \'11/07/2019\', \'/HomeAccess/Content/Student/DailySchedule.aspx\');" >

                                Thursday

                                    </span>
                            </div>
                            11/07
                            <div class="sg-right">Day: R                            </div>
                        </td>
                        <td class="sg-cell-width">
                            <div>
                                    <a title= \'Schedule for Friday 11/08\' href="javascript:SunGard.Hac.Home.ViewSchedulePopUp(\'24337     \', \'11/08/2019\', \'/HomeAccess/Content/Student/DailySchedule.aspx\');" >

                                Friday

                                    </span>
                            </div>
                            11/08
                            <div class="sg-right">Day: F                            </div>
                        </td>
                </tr>
           </thead>
           <tbody>

                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160195, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Activity 7</a>

                                <div>
                                <span>(AC701      - 7)</span>
                                <span>Per: 1    </span>
                                </div>

                                        <a href="mailto:ebuchana@ecusd7.org" id="staffName">
                                             Buchana, Elizabeth
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160195, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');"></a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                            <div>
                                                <div style="color:#ff0000 ;" class="sg-topleft-triangle sg-asp-table-no-top-padding sg-asp-table-no-left-padding"></div>
                                                <span style="color:#ff0000 ;">UNEXCUSED</span>
                                            </div>
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160225, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Math 7</a>

                                <div>
                                <span>(MA701      - 1)</span>
                                <span>Per: 2    </span>
                                </div>

                                        <a href="mailto:mwedekind@ecusd7.org" id="staffName">
                                             Wedekind, Megan
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160225, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">68.01</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160451, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Spanish 7</a>

                                <div>
                                <span>(SP701      - 4)</span>
                                <span>Per: 3    </span>
                                </div>

                                        <a href="mailto:cstiening@ecusd7.org" id="staffName">
                                             Stiening, Christa
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160451, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">96.83</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Family W.S.
Category: In Class Assignment
Due Date: 11/04/2019
Max Points: 29.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'3\', null, null, null, 160451, 1);">Family W.S. </a>

                                                    <span class="sg-right">
                                                        29/29
                                                                                                            </span>
                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Family/Adjective Magazine Project
Category: Project
Due Date: 11/05/2019
Max Points: 20.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'4\', null, null, null, 160451, 1);">Family/Adjective Magazine Project</a>

                                                    <span class="sg-right">
                                                        20/20
                                                                                                            </span>
                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160295, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Science 7</a>

                                <div>
                                <span>(SC701      - 2)</span>
                                <span>Per: 4    </span>
                                </div>

                                        <a href="mailto:hblythe@ecusd7.org" id="staffName">
                                             Blythe, Hope
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160295, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">59.80</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Interactivity Biodiversity in Amazon
Category: Homework
Due Date: 11/05/2019
Max Points: 15.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'42\', null, null, null, 160295, 1);">Interactivity Biodiversity in Amazon</a>

                                                    <span class="sg-right">
                                                        Z
                                                                                                                    <img src="/HomeAccess/Media/images/Legacy/Notes.png" title="" class="assignment-comment" data-comment="Not complete."/>
                                                    </span>
                                            </span>
                                        </div>
                                        <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Interactivity Ecosystem Services
Category: Homework
Due Date: 11/05/2019
Max Points: 15.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'43\', null, null, null, 160295, 1);">Interactivity Ecosystem Services</a>

                                                    <span class="sg-right">
                                                        15/15
                                                                                                            </span>
                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Letter Draft
Category: Homework
Due Date: 11/07/2019
Max Points: 5.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'44\', null, null, null, 160295, 1);">Letter Draft</a>

                                                    <span class="sg-right">
                                                        5/5
                                                                                                            </span>
                                            </span>
                                        </div>
                                        <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: 5-4 Guided Reading
Category: Homework
Due Date: 11/07/2019
Max Points: 11.50
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'45\', null, null, null, 160295, 1);">5-4 Guided Reading</a>

                                            </span>
                                        </div>
                                        <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Notebook
Category: Homework
Due Date: 11/07/2019
Max Points: 15.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'46\', null, null, null, 160295, 1);">Notebook</a>

                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160503, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Physical Ed</a>

                                <div>
                                <span>(PE702      - 7)</span>
                                <span>Per: 5    </span>
                                </div>

                                        <a href="mailto:tholler@ecusd7.org" id="staffName">
                                             Holler, Tanya
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160503, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">70.00</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160249, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Language Arts 7</a>

                                <div>
                                <span>(LA701      - 4)</span>
                                <span>Per: 6    </span>
                                </div>

                                        <a href="mailto:crobertson@ecusd7.org" id="staffName">
                                             Robertson, Cynthia
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160249, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">80.10</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Reading Log
Category: Homework
Due Date: 11/04/2019
Max Points: 25.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'32\', null, null, null, 160249, 1);">Reading Log</a>

                                                    <span class="sg-right">
                                                        22/25
                                                                                                            </span>
                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                            <div class="sg-clearfix">
                                            <span class="sg-left sg-assignment-description" title="Type: Assignment Course
Title: Freak the Mighty test
Category: Quiz/Test/Timed Writings
Due Date: 11/05/2019
Max Points: 100.00
Can Be Dropped: N
Extra Credit: N
Has Attachments: N">
                                                <a id="courseAssignmentDescription" href="#" onclick="SunGard.Hac.SharedClasswork.OpenAssignmentDialog(\'33\', null, null, null, 160249, 1);">Freak the Mighty test</a>

                                                    <span class="sg-right">
                                                        85/100
                                                                                                            </span>
                                            </span>
                                        </div>
                                </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sg-5px-margin">

                                    <a id="courseName" class="sg-font-larger" href="javascript:SunGard.Hac.Home.ViewClassPopUp(160273, 2, \'/HomeAccess/Content/Student/ClassPopUp.aspx\');">Soc Studies 7</a>

                                <div>
                                <span>(SS701      - 5)</span>
                                <span>Per: 7    </span>
                                </div>

                                        <a href="mailto:jnunn@ecusd7.org" id="staffName">
                                             Nunn, Jordan
                                        </a>
                            </div>
                        </td>

                            <td>
                                    <a id="average" class="sg-font-larger-average" href="javascript:SunGard.Hac.Home.ViewAssignmentsRCPopUp(160273, 1, \'QTR  .Trim()\', 2, \'/HomeAccess/Content/Student/AssignmentsFromRCPopUp.aspx\');">90.09</a>
                            </td>

                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                            <td>
                                <div class="sg-att-table-cell">
                                                                    </div>

                            </td>
                    </tr>

            </tbody>
        </table>
    </div>
    </div>
</form>




</div>


    <div class="sg-main-footer">
        <img src="/HomeAccess/Media/images/Footer/hac-footer-logo.png" alt="Home Access Center" class="sg-hac-footer-logo" />
<span class="sg-hac-copyright">&copy; 2003 - 2019 PowerSchool.  All Rights Reserved.</span>
<span class="sg-hac-footer-links">
        <a href="javascript:SunGard.Hac.Frame.Footer.DisplayFooterPopup(\'/HomeAccess/Frame/PrivacyPolicy\', \'Privacy Policy\')">
            Privacy Policy
        </a>

</span>
    </div>






        <script>
            $(function() {
                // Build the jQuery UI buttons
                $(\'.sg-buttonset\').buttonset();
                $(\'.sg-button\').button();
            })
        </script>
    </body>





</html>


    ';
}
