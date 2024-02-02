<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Measurement\MeasurementUnitIdProperty;
class fixInvalidMeasurementUnitIdPropertyRecordsTest extends JobTestCase
{
    public function testFixInvalidMeasurementUnitIdPropertyRecords(): void{
		MeasurementUnitIdProperty::fixInvalidRecords();
	}
}
