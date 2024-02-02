<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\AppSettings\AppDesign\Menu\MenuItem;
use App\AppSettings\AppDesign\Menu\MenuSettings;
use App\Buttons\QMButton;
use App\Files\FileHelper;
use App\Menus\SearchMenu;
use App\Models\Application;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\States\IonicState;
use App\Slim\Model\User\QMUser;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\QMColor;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use PHPUnit\Framework\Assert;
use Tests\QMAssert;
use Tests\SlimStagingTestCase;
class MenuTest extends SlimStagingTestCase
{
    public function testSearchMenu(){
        $this->assertEquals("oAuth Client",  QMStr::classToTitle(OAClient::class));
        $m = SearchMenu::instance();
        $this->compareMenuButtons($m);
        $html = $m->getMaterialStatCards();
        $this->compareHtmlFragment('MaterialStatCards', $html, false, $m);
    }
    public function testAdminSearchMenu(){
	    $this->setAdminUser();
        $buttons = SearchMenu::buttons();
        $names = collect($buttons)->pluck('title')->all();
        $this->assertContains("Users", $names);
        $m = SearchMenu::instance();
        $this->assertButtonTitles(array (
	                                  'About Us' => 'About Us',
	                                  'Admin Search' => 'Admin Search',
	                                  'Global Variable Relationships' => 'Global Variable Relationships',
	                                  'Applications' => 'Applications',
	                                  'Bugsnag' => 'Bugsnag',
	                                  'Clockwork' => 'Clockwork',
	                                  'Collaborators' => 'Collaborators',
	                                  'Common Tags' => 'Common Tags',
	                                  'Connections' => 'Connections',
	                                  'Connector Imports' => 'Connector Imports',
	                                  'Connector Requests' => 'Connector Requests',
	                                  'Connectors' => 'Connectors',
	                                  'Contact Us' => 'Contact Us',
	                                  'VariableRelationships' => 'VariableRelationships',
	                                  'Device Tokens' => 'Device Tokens',
	                                  'Horizon Queue Manager' => 'Horizon Queue Manager',
	                                  'Issues' => 'Issues',
	                                  'Measurement Exports' => 'Measurement Exports',
	                                  'Measurement Imports' => 'Measurement Imports',
	                                  'Measurements' => 'Measurements',
	                                  'Messages' => 'Messages',
	                                  'Need Help?' => 'Need Help?',
	                                  'Notifications' => 'Notifications',
	                                  'Open Source' => 'Open Source',
	                                  'Posts' => 'Posts',
	                                  'Purchases' => 'Purchases',
	                                  'Start Tracking' => 'Start Tracking',
	                                  'Studies' => 'Studies',
	                                  'Subscriptions' => 'Subscriptions',
	                                  'Telescope' => 'Telescope',
	                                  'Tracking Reminder Notifications' => 'Tracking Reminder Notifications',
	                                  'Tracking Reminders' => 'Tracking Reminders',
	                                  'Unit Categories' => 'Unit Categories',
	                                  'Units' => 'Units',
	                                  'User Tags' => 'User Tags',
	                                  'User Variables' => 'User Variables',
	                                  'Users' => 'Users',
	                                  'Variable Categories' => 'Variable Categories',
	                                  'Variables' => 'Variables',
	                                  'Votes' => 'Votes',
	                                  'oAuth Access Tokens' => 'oAuth Access Tokens',
	                                  'oAuth Clients' => 'oAuth Clients',
                                  ), $m->getButtons());
        $html = $m->getHiddenSearchMenuList();
        // Doesn't work because IP always changes $this->compareHtmlFragment("search-menu-for-admin",$html);
    }
    public function testRelatedDataMenuIcon(){
        $user = QMUser::getAnyOldTestUser();
        $l = $user->l();
        $menu = $l->getDataLabRelationshipMenu();
        $this->compareMenuButtons($menu);
        $b = $menu->getDropDownMenu();
        $this->assertContains(FontAwesome::RELATIONSHIPS, $b);
    }
    public function testRelatedDataMenuTooltips(){
        $user = QMUser::getAnyOldTestUser();
        $l = $user->l();
        $menu = $l->getDataLabRelationshipMenu();
        $measurements = $menu->getButtonByTitle(Measurement::TABLE);
        $this->assertEquals(Measurement::CLASS_DESCRIPTION, $measurements->getTooltip());
        $buttons = $this->compareMenuButtons($menu);
        $prefixesToStrip = array_keys(QMStr::PREFIXES_TO_REPLACE);
        foreach($buttons as $button){
            $this->assertNotEmpty($button->tooltip, "$button->title button missing tooltip");
            QMAssert::assertStringDoesNotContain($button->getTooltip(), $prefixesToStrip,
                $button->getTitleAttribute()." Button Tooltip");
            $this->assertNotEmpty($button->title);
            QMAssert::assertStringDoesNotContain($button->getTitleAttribute(), $prefixesToStrip,
                $button->getTitleAttribute()." Button Title");
        }
    }
    public function testAnalyzableIndexMenu(){
	    $this->setAdminUser();
        $v = OverallMoodCommonVariable::instance();
        $l = $v->l();
        $menu = $l->getDataLabIndexMenu();
        $buttons = $this->compareMenuButtons($menu);
        $titles = collect($buttons)->pluck('title')->all();
        $this->assertEquals([
            0 => 'Recycle Bin',
            1 => 'Analysis Progress',
            2 => 'Never Analyzed',
            3 => 'Failed Analyses',
            ], $titles);
    }
    public function testSingleModelMenu(){
        $u = QMUser::getAnyOldTestUser();
        $n = $u->getOrCreateUserVariable(OverallMoodCommonVariable::NAME);
        $this->setAuthenticatedUser($u->getUserId());
        $u = QMAuth::getQMUser();
        if($u->isAdmin()){le('$u->isAdministrator()');}
        $menu = $n->getDataLabSingleModelMenu();
        $buttons = $this->compareMenuButtons($menu);
        $titles = [];
        foreach($buttons as $b){
            $t = $b->getTitleAttribute();
            if(in_array($t, $titles)){le("Duplicate button: $t");}
            $titles[] = $t;
        }
        $this->assertEquals([
            0 => 'Open',
            1 => 'Edit',
            2 => 'Delete',
            3 => 'Analyze',
            4 => 'Correlate'], $titles);
    }
    public function testIntro(){
        $appSettings = Application::getClientAppSettings('moodimodo');
        $introString = json_encode($appSettings->appDesign->intro->active);
        $this->assertTrue(str_contains($introString, "I'm MoodiModo"));
        $this->assertTrue(!str_contains($introString, "QuantiModo"));
    }
    public function testFixMenuItem(){
        $appSettings = Application::getClientAppSettings(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $appSettings->appDesign->menu = FileHelper::getDecodedJsonFile(FileHelper::absPath("tests/SlimTests/AppSettings/menu.json"));
        $appSettings->appDesign->menu = new MenuSettings($appSettings);
        MenuSettings::checkMenu($appSettings->appDesign->menu->active);
        MenuSettings::checkMenu($appSettings->appDesign->menu->custom);
        $this->assertNotNull($appSettings->appDesign->menu->active);
    }
    public function testMenu(){
        $stateName = "app.variableList";
        $menuItem = '{"title":"Vital Signs","params":{"variableCategoryName":null},"icon":"ion-ios-pulse",
            "href":"#\/app\/variable-list?variableCategoryName=Vital%20Signs","id":"variable-list_variablecategoryname_vital%20signs",
            "stateName":"'.$stateName.'","$$hashKey":"object:331"}';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        $this->assertContains("Vital+Signs", $menuItem->href);
        $this->assertEquals("Vital Signs", $menuItem->params->variableCategoryName);
        $state = IonicState::getByName($stateName);
        $this->assertEquals($stateName, $state->getNameAttribute());
        $menuParams = $menuItem->getParams();
        foreach ($state->getParams() as $key => $value){
            if(!property_exists($menuParams, $key)){
                throw new \LogicException("$key not in menu params");
            }
        }
	}
    public function testProcessMenuItemHref(){
        $menuItem = '{"title":"Vital Signs","params":{"variableCategoryName":null},"icon":"ion-ios-pulse","href":"#\/app\/variable-list?variableCategoryName=Vital%20Signs","id":"variable-list_variablecategoryname_vital%20signs","stateName":"app.variableList","$$hashKey":"object:331"}';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        Assert::assertEquals("Vital Signs", $menuItem->params->variableCategoryName);
    }
    public function testUrlStateParamInMenuItemHref(){
        $menuItem = '{
            "stateName": "app.charts",
            "href": "#/app/charts/:variableNameshowAds=true&trackingReminder=null&variableObject=null&measurementInfo=null&noReload=false&fromState=null&fromUrl=null&refresh=null&title=Charts&ionIcon=ion-arrow-graph-up-right&hideLineChartWithoutSmoothing=false&hideLineChartWithSmoothing=false&hideMonthlyColumnChart=false&hideWeekdayColumnChart=false&hideDistributionColumnChart=false&variableName=null",
            "url": null,
            "icon": "ion-ios-pulse",
            "subMenu": null,
            "params": {
                "showAds": true,
                "trackingReminder": null,
                "variableObject": null,
                "measurementInfo": null,
                "noReload": false,
                "fromState": null,
                "fromUrl": null,
                "refresh": null,
                "title": "Charts",
                "ionIcon": "ion-arrow-graph-up-right",
                "hideLineChartWithoutSmoothing": true,
                "hideLineChartWithSmoothing": false,
                "hideMonthlyColumnChart": false,
                "hideWeekdayColumnChart": false,
                "hideDistributionColumnChart": false,
                "variableName": "Heart Rate (Pulse)"
            },
            "title": "Heart Rate (Pulse)",
            "id": "charts-:variablenameshowads=true&trackingreminder=null&variableobject=null&measurementinfo=null&noreload=false&fromstate=null&fromurl=null&refresh=null&title=charts&ionicon=ion-arrow-graph-up-right&hidelinechartwithoutsmoothing=false&hidelinechartwithsmoothing=false&hidemonthlycolumnchart=false&hideweekdaycolumnchart=false&hidedistributioncolumnchart=false&variablename=null",
            "showSubMenu": false
        }';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        $this->assertEquals("charts-heart-rate-pulse", $menuItem->id);
        $this->assertEquals("#/app/charts/Heart+Rate+%28Pulse%29?hideLineChartWithoutSmoothing=1&variableName=Heart+Rate+%28Pulse%29", $menuItem->href);
    }
    public function testCategoryHistoryId(){
        $menuItem = '{
            "stateName": "app.historyAllCategory",
            "href": "#/app/history-all-category/Symptoms-category/Symptoms",
            "url": "/history-all-category/:variableCategoryName",
            "icon": "ion-sad-outline",
            "subMenu": null,
            "params": {
                "showAds": true,
                "updatedMeasurementHistory": null,
                "refresh": null,
                "title": "History",
                "ionIcon": "ion-ios-list-outline"
            },
            "title": "Symptoms",
            "id": "historyallcategory-symptoms",
            "showSubMenu": null
        }';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        $params = $menuItem->params;
        $this->assertEquals("ion-sad-outline", $params->ionIcon);
        $this->assertEquals("history-all-category-symptoms", $menuItem->id);
        $this->assertEquals("#/app/history-all-category/Symptoms?variableCategoryName=Symptoms", $menuItem->href);
    }
    public function testSettingsMenuItem(){
        $menuItem = '{
						"stateName": "app.settings",
						"href": "#/app/settings",
						"url": "/settings",
						"icon": "ion-ios-gear-outline",
						"subMenu": null,
						"params": {
							"title": "Settings",
							"ionIcon": "ion-settings"
						},
						"title": "Settings",
						"id": "settings",
						"showSubMenu": null
					}';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        $params = $menuItem->params;
        $this->assertEquals("ion-ios-gear-outline", $params->ionIcon);
        $this->assertEquals("settings", $menuItem->id);
        $this->assertEquals("#/app/settings", $menuItem->href);
    }
    public function testPreveMenuItem(){
        $menuItem = '{
						"stateName": "app.chartsCategory",
						"href": "#/app/charts/:variableName-category/Physical+Activity?showAds=true&trackingReminder=null&variableObject=null&measurementInfo=null&noReload=false&fromState=null&fromUrl=null&refresh=null&title=Charts&ionIcon=ion-arrow-graph-up-right&hideLineChartWithoutSmoothing=true&hideLineChartWithSmoothing=true&hideMonthlyColumnChart=false&hideWeekdayColumnChart=false&hideDistributionColumnChart=true&variableName=Calories%20Burned",
						"icon": "ion-ios-gear-outline",
						"subMenu": null,
						"params": {
							"title": "Calories Burned",
							"ionIcon": "ion-settings",
							"variableName": "Calories Burned",
							"hideDistributionColumnChart": true
						},
						"title": "Calories Burned",
						"id": "charts-:variablename-category/physical+activityshowads-true-trackingreminder=null&variableobject=null&measurementinfo=null&noreload=false&fromstate=null&fromurl=null&refresh=null&title=charts&ionicon=ion-arrow-graph-up-right&hidelinechartwithoutsmoothing=true&hidelinechartwithsmoothing=true&hidemonthlycolumnchart=false&hideweekdaycolumnchart=false&hidedistributioncolumnchart=true&variablename=calories%20burned",
						"showSubMenu": null
					}';
        /** @var MenuItem $menuItem */
        $menuItem = json_decode($menuItem);
        $menuItem = new MenuItem($menuItem);
        $params = $menuItem->params;
        $this->assertEquals("charts-calories-burned", $menuItem->id);
        $this->assertEquals("#/app/charts/Calories+Burned?ionIcon=ion-settings&title=Calories+Burned&variableName=Calories+Burned&hideDistributionColumnChart=1", $menuItem->href);
    }
    public function testAvatarBadgesList(){
        $u = QMUser::getAnyOldTestUser();
        $menu = $u->getRelationshipsMenu();
        $b = $menu->getButtonByTitle("Purchases where this is the Subscriber User");
        $this->assertNull($b);
        $buttons = $this->compareMenuButtons($menu);
        $this->assertGreaterThan(5, count($buttons));
        $stringColors = QMColor::getStringColors();
        foreach($buttons as $b){
            $this->assertNotNull($b->getBackgroundColor(), $b->getTitleAttribute()." has no color!");
            $box = $b->getStatBox();
            $color = QMStr::between($box, "bg-", '"');
            $this->assertContains($color, $stringColors, "bg- css requires a string color, not bootstrap");
            $this->assertNotNull($b->badgeText);
        }
    }
    /**
     * @param $m
     * @return QMButton[]
     */
    protected function compareMenuButtons($m): array {
        $buttons = $m->getButtons();
        $buttons = QMArr::unsetNullAndEmptyArrayOrStringProperties($buttons);
        $this->compareObjectFixture("buttons", $buttons);
        return $buttons;
    }
}
