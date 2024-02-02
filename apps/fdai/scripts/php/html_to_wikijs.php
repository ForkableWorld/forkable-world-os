<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Types\QMStr;
require_once __DIR__.'/bootstrap_script.php';
$wikiDir = "/www/wwwroot/wiki-js-backup";
$htmlDir = "/www/wwwroot/mediawiki-html/export";
$files = FileFinder::listFiles("$htmlDir");
foreach($files as $htmlFile){
	$contents = file_get_contents($htmlFile);
	$name = basename($htmlFile);
	$title = str_replace(".html", "", $name);
	$title = str_replace("_", " ", $title);
	$contents = QMStr::between($contents, "<body>", "</body>");
	$contents = "<!--
title: $title
description: 
published: true
date: 2021-12-04T01:35:45.628Z
tags: 
editor: ckeditor
dateCreated: 2021-03-18T02:07:00.123Z
-->".$contents;
	$contents = str_replace("â€™", "'", $contents);
	$contents = str_replace("Î²", "β", $contents);
	$contents = str_replace("â€™", "'", $contents);
	$contents = str_replace("â€�", "'", $contents);
	$contents = str_replace("â€œ", "'", $contents);
	$contents = str_replace("â€™", "'", $contents);
	$contents = str_replace("â€™", "'", $contents);
	$contents = str_replace("â€�â€”", "' ", $contents);
	$contents = str_replace("Â", "", $contents);
	FileHelper::write($wikiDir."/wiki/".$name, $contents);
}
