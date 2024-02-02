<?php
use App\Storage\DatabaseSynchronizer;
use App\Storage\DB\ProductionDB;
use App\Storage\DB\ProductionPgGcpDB;
require_once __DIR__.'/../scripts/php/bootstrap_script.php';
ProductionPgGcpDB::migrateTables();
DatabaseSynchronizer::syncStatic(ProductionDB::db(), ProductionPgGcpDB::db());
