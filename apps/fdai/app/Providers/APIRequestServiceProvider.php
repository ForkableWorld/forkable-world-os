<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
class APIRequestServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     * @return void
     */
    public function register(){
        //
    }
    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot(){
        $this->app['events']->listen('router.matched', function(){
            $this->app->resolving(function(APIRequest $request, $app){
                $this->initializeRequest($request, $app['request']);
                $request->setContainer($app);
            });
        });
    }
    /**
     * Initialize the form request with data from the given request.
     * @param \Illuminate\Http\Request $api
     * @param Request $current
     */
    protected function initializeRequest(APIRequest $api, Request $current){
        $files = $current->files->all();
        $files = is_array($files) ? array_filter($files) : $files;
        $api->initialize($current->query->all(), $current->request->all(), $current->attributes->all(), $current->cookies->all(), $files, $current->server->all(), $current->getContent());
        if($session = $current->getSession()){
            $api->setSession($session);
        }
        $api->setUserResolver($current->getUserResolver());
        $api->setRouteResolver($current->getRouteResolver());
    }
}
