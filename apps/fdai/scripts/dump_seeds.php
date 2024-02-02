<?php

use App\Models\OAClient;
putenv("APP_ENV=testing");
require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
OAClient::reprocessSeed();
//TestDB::migrate();
//(new DatabaseSeeder())->run();
////Variable::dumpTestFixture();
//TestDB::dumpFixtures();
