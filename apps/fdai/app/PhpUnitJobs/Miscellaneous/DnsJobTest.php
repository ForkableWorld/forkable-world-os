<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Miscellaneous;
use hanneskod\classtools\Exception\LogicException;
use App\DevOps\CloudFlareHelper;
use App\PhpUnitJobs\JobTestCase;
class DnsJobTest extends JobTestCase {
    public function testCreateDnsRecordsForAllClients(){
        le("Not implemented");
        CloudFlareHelper::createDnsRecordAndNetlifyAlias("mindfirst");
        CloudFlareHelper::createDnsRecordsForAllClients();
    }
}
