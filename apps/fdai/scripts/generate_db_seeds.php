<?php
use App\Storage\DB\TestDB;
putenv('APP_ENV=testing');
require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
TestDB::generateSeeds();
