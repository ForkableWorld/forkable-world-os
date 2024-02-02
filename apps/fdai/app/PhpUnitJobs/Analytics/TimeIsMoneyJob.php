<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Analytics;
use App\DataSources\Connectors\GoogleCalendarConnector;
use App\PhpUnitJobs\JobTestCase;
class TimeIsMoneyJob extends JobTestCase {
    /**
     * @throws \OAuth\Common\Exception\Exception
     * @throws \OAuth\Common\Token\Exception\ExpiredTokenException
     * @throws \App\Exceptions\CredentialsNotFoundException
     */
    public function testTimeIsMoney(){
        $calendar = GoogleCalendarConnector::getByUserId(230);
        $measurements = $calendar->importData();
    }
}
