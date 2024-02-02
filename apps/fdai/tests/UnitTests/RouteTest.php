<?php
namespace Tests\UnitTests;
use App\Menus\Routes\DataLabRoutesMenu;
use App\Utils\QMRoute;
use Tests\QMAssert;
use Tests\UnitTestCase;
class RouteTest extends UnitTestCase
{
    public function testRouteTitles(){
        $routes = DataLabRoutesMenu::getRoutes();
        $titles = collect($routes)->map(function($m){
            /** @var QMRoute $m */
            $t = $m->getTitleAttribute();
            try {
                QMAssert::assertStringDoesNotContain($t, ['{'], $m->getName()." menu item", false);
            } catch (\Throwable $e){
                $m->title = $m->titleFromName = $m->titleFromUrl = null;
                $t = $m->getTitleAttribute();
            }
            QMAssert::assertStringDoesNotContain($t, ['{'], $m->getName()." menu item", false);
            return $t;
        })->all();
        $this->assertContains("List Measurement Imports", $titles);
    }
}
