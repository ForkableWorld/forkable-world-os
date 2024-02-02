<?php
use App\Models\OAClient;
use App\Models\User;
putenv("APP_ENV=testing");
require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
User::deleteAll();
User::system();
User::demo();
User::getAdminUser();
User::mike();
User::physician();
User::patient();
