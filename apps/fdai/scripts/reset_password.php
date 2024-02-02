<?php
use App\Models\User;
require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
$mike = User::mike();
$mike->setPlainTextPassword(getenv('PASSWORD'));
$mike->save();
