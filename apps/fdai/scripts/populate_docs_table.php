<?php /** @noinspection PhpUnhandledExceptionInspection */
use App\Files\Spreadsheet\CsvFile;
use App\Storage\DB\TestDB;
require_once __DIR__.'/../scripts/php/bootstrap_script.php';
//TestDB::migrate();
//CsvFile::jsonToCsv('data/property_models.json');
TestDB::populateDocsTable();
