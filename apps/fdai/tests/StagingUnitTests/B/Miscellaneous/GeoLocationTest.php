<?php
namespace Tests\StagingUnitTests\B\Miscellaneous;
use App\Models\User;
use App\Utils\GeoLocation;
use Tests\SlimStagingTestCase;
/**
 * @coversDefaultClass \App\Utils\GeoLocation
 * @covers \App\Utils\GeoLocation::getLongitude
 * @covers \App\Utils\GeoLocation::getLatitude
 */
class GeoLocationTest extends SlimStagingTestCase {
    //protected const DISABLED_UNTIL = "2021-11-02";
	/**
	 * @coversDefaultClass \App\Utils\GeoLocation
	 * @covers \App\Utils\GeoLocation::getLongitude
	 * @covers \App\Utils\GeoLocation::getLatitude
	 * @covers \App\Utils\GeoLocation::ipData
	 */
	public function testGeoLocation(){
        if($this->weShouldSkip()){return;}
        $mike = User::mike();
        $zip = $mike->zip_code;
        $row = $mike->l();
        if(time() > strtotime("2019-08-17")){
            if($zip !== "62034" && $zip !== "62025"){
                $row = $mike->l();
                $this->assertNotEquals("62034", $row->zip_code);
                le("$zip is wrong!");
            }
        }
        $geoLocation = GeoLocation::ipData("24.216.163.200");
        $this->assertNotNull($geoLocation->longitude);
        $this->assertNotNull($geoLocation->latitude);
        $this->assertEquals("62025", $geoLocation->zip);
    }
}
