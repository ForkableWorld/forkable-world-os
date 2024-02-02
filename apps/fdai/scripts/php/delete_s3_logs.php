<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Storage\S3\S3Private;
require_once __DIR__ . '/bootstrap_script.php';
$items = S3Private::deleteFilesStaringWith("2021-");
