<?php
use App\Logging\QMLog;
use App\Storage\DB\ProductionPgGcpDB;
require_once __DIR__.'/../scripts/php/bootstrap_script.php';
$images = array (
	0 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Healthcare/pill-96.png',
		),
	1 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Messaging/sad-96.png',
		),
	2 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Food/vegetarian_food-96.png',
		),
	3 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Cinema/theatre_mask-96.png',
		),
	4 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Sports/weightlifting-96.png',
		),
	5 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Healthcare/virus-96.png',
		),
	6 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Programming/console-96.png',
		),
	7 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Weather/chance_of_storm-96.png',
		),
	8 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Sports/football-96.png',
		),
	9 =>
		array (
			'image_url' => 'https://maxcdn.icons8.com/Color/PNG/96/Users/dizzy_person_2-96.png',
		),
);
foreach($images as $badImage) {
	$badImage = $badImage['image_url'];
	$filename = basename($badImage);
	$newUrl = 'https://static.quantimo.do/img/variable_categories/'.$filename;
	$res = ProductionPgGcpDB::getDBTable('variables')
		->where('image_url', $badImage)
		->update(array('image_url' => $newUrl));
	QMLog::info("Updated $res rows with $badImage to $newUrl");
}
