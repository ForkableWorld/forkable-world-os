<?php
use App\Storage\DefaultDB;
use InfyOm\Generator\Commands\APIScaffoldGeneratorCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$app = require_once __DIR__ . '/../scripts/php/bootstrap_script.php';
//BaseModelFile::generateByTable('nfts');
$tables = DefaultDB::getTableNames();
foreach ($tables as $table) {
	$table = 'nfts';
    $class = Str::studly(Str::singular($table));
    $c = new APIScaffoldGeneratorCommand();
    $params = [
        'model' => $class,
        '--fromTable' => true,
        '--table' => $table,
        //'--skip' => 'migration,tests,datatables,requests,api_requests,factories,seeder,repository,api_controller,
        '--skip' => 'views,menu',
        //api_routes,api_test,repository_test,menu,views,tests,api_resources',
        //'--force' => true,
        //'--connection' => QuAnTiMoDoPostgresDB::CONNECTION_NAME,
    ];
    $c->setLaravel($app);
    $c->setInput(new ArrayInput($params));
    $c->run(new ArrayInput($params), new ConsoleOutput(\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG));
    $c->handle();
    //Artisan::call('generate:everything', $params);
}
