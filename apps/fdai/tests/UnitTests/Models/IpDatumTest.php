<?php
namespace Tests\UnitTests\Models;
use App\Models\IpDatum;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Models\IpDatum
 */
class IpDatumTest extends UnitTestCase {
	/**
	 * @covers \App\Models\IpDatum::fill
	 */
	public function testIpDatumFill(){
        $this->skipTest("TODO: testIpDatumFill");
		$u = /*(n*/\App\Models\User::find(1);
		$ip = '24.216.168.142';
		$ipData = $u->getIpGeoLocation($ip);
		//$ipData->generateProperties();
		$zip = '62025';
		$lat = round('38.81370');
		$long = round('-89.95629');
		$tzName = 'America/Chicago';
		$tzOffset = -6;
		/*(n*/\App\Models\IpDatum::whereIp($ip)->forceDelete();
		$m = new /*(n*/\App\Models\IpDatum([
			'ip' => $ip,
			'continent_code' => 'NA',
			'continent_name' => 'North America',
			'country_code2' => 'US',
			'country_code3' => 'USA',
			'country_name' => 'United States',
			'country_capital' => 'Washington, D.C.',
			'state_prov' => 'Illinois',
			'district' => 'Madison',
			'city' => 'Edwardsville',
			'zipcode' => $zip,
			'latitude' => $lat,
			'longitude' => $long,
			'is_eu' => false,
			'calling_code' => '+1',
			'country_tld' => '.us',
			'languages' => 'en-US,es-US,haw,fr',
			'country_flag' => 'https://ipgeolocation.io/static/flags/us_64.png',
			'geoname_id' => '4237722',
			'isp' => 'Charter Communications',
			'connection_type' => '',
			'organization' => 'Charter Communications',
			'currency' =>
				(object) [
					'code' => 'USD',
					'name' => 'US Dollar',
					'symbol' => '$',
				],
			'time_zone' =>
				(object) [
					'name' => $tzName,
					'offset' => $tzOffset,
					'current_time' => '2021-10-31 14:57:20.336-0500',
					'current_time_unix' => 1635710240.336,
					'is_dst' => true,
					'dst_savings' => 1,
				],
		]);
		$m->save();
		$this->assertNotNull($m->id);
		$m = /*(n*/\App\Utils\GeoLocation::ipData($ip);
		$this->assertEquals($zip, $m->zip);
		$this->assertEquals($ip, $m->ip);
		$this->assertEquals($lat, round($m->latitude));
		$this->assertEquals($long, round($m->longitude));
		$this->assertEquals($tzName, $m->time_zone_name);
	}
	public function createSaveTest(){
		$model = new \App\Models\IpDatum();
		                    $model->populate(array (
		  'city' => 'Edwardsville',
		  'continent_code' => 'NA',
		  'continent_name' => 'North America',
		  'country_code' => 'US',
		  'country_name' => 'United States',
		  'created_at' => '2022-09-14 02:26:28',
		  'currency' =>
		  array (
		    'code' => 'USD',
		    'name' => 'US Dollar',
		    'symbol' => '$',
		  ),
		  'ip' => '24.216.168.142',
		  'latitude' => 39.0,
		  'location' =>
		  array (
		    'country_code3' => 'USA',
		    'country_capital' => 'Washington, D.C.',
		    'state_prov' => 'Illinois',
		    'district' => 'Madison',
		    'is_eu' => false,
		    'calling_code' => '+1',
		    'country_tld' => '.us',
		    'languages' => 'en-US,es-US,haw,fr',
		    'country_flag' => 'https://ipgeolocation.io/static/flags/us_64.png',
		    'geoname_id' => '4237722',
		    'isp' => 'Charter Communications',
		    'connection_type' => '',
		    'organization' => 'Charter Communications',
		  ),
		  'longitude' => -90.0,
		  'time_zone' =>
		  array (
		    'name' => 'America/Chicago',
		    'offset' => -6,
		    'current_time' => '2021-10-31 14:57:20.336-0500',
		    'current_time_unix' => 1635710240.336,
		    'is_dst' => true,
		    'dst_savings' => 1,
		  ),
		  'timezone' => NULL,
		  'updated_at' => '2022-09-14 02:26:28',
		  'zip' => '62025',
		));
        $model->save();
	}
}
