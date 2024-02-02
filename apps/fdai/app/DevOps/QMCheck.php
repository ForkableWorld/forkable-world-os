<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Storage\DB\TdddDB;
use Spatie\ServerMonitor\Models\Check;
class QMCheck extends Check {
	public $connection = TdddDB::CONNECTION_NAME;
}
