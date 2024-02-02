<?php
use App\Storage\DatabaseSynchronizer;
require_once __DIR__ . '/../bootstrap/bootstrap_script.php';
DatabaseSynchronizer::syncPublic();
