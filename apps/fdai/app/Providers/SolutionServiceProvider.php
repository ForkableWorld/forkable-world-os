<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use App\SolutionProviders\StagingUnitTestSolutionProvider;
use App\SolutionProviders\DirectoryNotFoundSolutionProvider;
use App\SolutionProviders\TestSolutionProvider;
use Facade\IgnitionContracts\SolutionProviderRepository;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
class SolutionServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(){
        $this->app->make(SolutionProviderRepository::class)->registerSolutionProviders([
            //UnspecifiedSolutionProvider::class,
            TestSolutionProvider::class,
            StagingUnitTestSolutionProvider::class,
            //QMMissingImportSolutionProvider::class, TODO: Maybe fix?  See class for details
            DirectoryNotFoundSolutionProvider::class,
        ]);
    }
    public function register(){
        parent::register();
    }
}
