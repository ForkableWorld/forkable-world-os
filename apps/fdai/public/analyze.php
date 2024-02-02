<?php
//GUZZLE INSTALLATION
//composer require guzzlehttp/guzzle

use App\Utils\AppMode;
use Illuminate\Contracts\Http\Kernel;

require __DIR__.'/../bootstrap/autoload.php';
AppMode::setIsApiRequest(true);

//REQUEST
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
/** @var Kernel $kernel */
$request = Illuminate\Http\Request::capture();
//$data = '{"uuid":"20361fef-e7df-45af-9890-9bc70c8bd7e5","hrv":{"2022-05-7":34,"2022-05-8":34,"2022-05-9":32,
//"2022-05-10":39,"2022-05-11":41,"2022-05-12":25,"2022-05-13":30},"steps":{"2022-05-7":8472,"2022-05-8":3402,"2022-05-9":3930,"2022-05-10":9909,"2022-05-11":4943,"2022-05-12":9012,"2022-05-13":1122}}';
if(!isset($data)){
    $data = $request->all();
}






