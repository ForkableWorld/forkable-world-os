<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Files\PHP\BaseModelFile;
use App\Models\Variable;
require_once __DIR__ . '/bootstrap_script.php';
$table = Variable::TABLE;
BaseModelFile::updateModelAndProperties($table);
