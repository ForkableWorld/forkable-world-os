<?php

use App\Storage\AbstractDB;

require_once __DIR__ . '/../bootstrap/bootstrap_script.php';

$res = AbstractDB::deleteLike('Unique Test Variable', 'variables', 'name');
