<?php
namespace Tests\UnitTests\Properties\Measurement;
use App\Properties\Measurement\MeasurementOriginalStartAtProperty;
use Tests\UnitTestCase;
/**
 * @covers MeasurementOriginalStartAtProperty
 */
class MeasurementOriginalStartAtPropertyTest extends UnitTestCase {
	/**
	 * @var MeasurementOriginalStartAtProperty::getSynonyms
	 */
	public function testGetSynonyms(){
		$synonyms = MeasurementOriginalStartAtProperty::getSynonyms();
		$this->assertArrayEquals(array (
            0 => 'original_start_at',
            2 => 'startTimeEpoch',
            3 => 'start_at',
            4 => 'timestamp',
            5 => 'startTime',
        ), $synonyms);
	}
}
