<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Studies;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Storage\DB\QMGoDaddyDB;
use App\Storage\DB\QMDB;
use App\Storage\DB\TBNDigitalOceanDB;
use App\Storage\DB\TBNGoDaddyDB;
use App\PhpUnitJobs\JobTestCase;
class WPJob extends JobTestCase
{
    public function testCopyTablesToGodaddy(){
        QMGoDaddyDB::copyToGodaddy();
    }
    public function testDeleteUserVariableUserStudyPopulationStudyPosts(){
        WpPost::deleteUserVariableUserStudyPopulationStudyPosts();
        WpPost::getCategoryCounts();
    }
    public function testCopyTBNImages(){
        QMDB::copyTable(TBNDigitalOceanDB::class, TBNGoDaddyDB::class, WpPostmetum::TABLE);
        QMDB::copyTable(TBNDigitalOceanDB::class, TBNGoDaddyDB::class, WpPost::TABLE);
    }
}
