<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;

use App\DataSources\LusitanianGuzzleClient;
use App\Logging\ConsoleLog;
use Clockwork\Clockwork;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Profiling\Clockwork\Profiler;
use GuzzleHttp\Profiling\Middleware;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Log\LoggerInterface;

class GuzzleClockworkServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            Client::class,
            ClientInterface::class,
            HandlerStack::class,
        ];
    }

    /**
     * Register method.
     */
    public function register(): void
    {
		try {
			// Configuring all guzzle clients.
			$this->app->bind(ClientInterface::class, function() {
				// Guzzle client
				//return new Client(['handler' => $this->app->make(HandlerStack::class)]);
				return new LusitanianGuzzleClient();
			});
		} catch (\Throwable $e) {
		    ConsoleLog::warning(__METHOD__.": ".$e->getMessage());
		}


        $this->app->alias(ClientInterface::class, Client::class);
        $this->app->alias(ClientInterface::class, PsrClientInterface::class);

        // Bind if needed.
        $this->app->bindIf(HandlerStack::class, function(): HandlerStack {
            return HandlerStack::create();
        });

        // If resolved, by this SP or another, add some layers.
        $this->app->resolving(HandlerStack::class, function(HandlerStack $stack): void {
            /** @var Clockwork $clockwork */
            $clockwork = $this->app->make('clockwork');
            $clockworkRequest = $clockwork->getRequest();

            $stack->push(new Middleware(new Profiler($clockworkRequest->timeline())));

            /** @var MessageFormatter $formatter */
            $formatter = $this->app->make(MessageFormatter::class);
            $stack->unshift(GuzzleMiddleware::log($clockworkRequest->log(), $formatter));

            // Also log to the default PSR logger.
            if ($this->app->bound(LoggerInterface::class)) {
                $logger = $this->app->make(LoggerInterface::class);

                // Don't log to the same logger twice.
                if ($logger === $clockworkRequest->log()) {
                    return;
                }

                // Push the middleware on the stack.
                $stack->unshift(GuzzleMiddleware::log($logger, $formatter));
            }
        });
    }
}
