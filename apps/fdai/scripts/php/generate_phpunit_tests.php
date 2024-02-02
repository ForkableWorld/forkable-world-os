<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\CodeGenerators\PhpUnitTestGenerator;
require_once __DIR__ . '/bootstrap_script.php';
PhpUnitTestGenerator::generateForFolder('app/Files');
