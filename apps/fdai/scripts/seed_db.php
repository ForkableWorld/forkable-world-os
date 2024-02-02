<?php
use App\Storage\DB\TestDB;
require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
TestDB::importAndMigrateTestDB();
