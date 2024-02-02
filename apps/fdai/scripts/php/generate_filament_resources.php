<?php
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Models\BaseModel;
use App\QMTerminal;
use App\Types\QMStr;
require_once __DIR__.'/bootstrap_script.php';
$classes = BaseModel::getClasses();
foreach($classes as $class){
	$class = QMStr::toShortClassName($class);
	QMTerminal::run("php artisan make:filament-resource $class --view --soft-deletes --generate");
}

$filamentClasses = FileHelper::listClassesInNamespace('App\Filament');
foreach($filamentClasses as $class){
	$resourceShortClass = QMStr::toShortClassName($class);
	$baseModelClass = '\App\Models\\'.str_replace('Resource', '', $class);
	$relations = (new $baseModelClass)->getRelations();
	foreach($relations as $relation){
		$relationShortClass = QMStr::toShortClassName($relation);
		QMTerminal::run("php artisan make:filament-relation-manager $resourceShortClass posts title");
	}
}
