<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit;

use App\DataSources\Connectors\Fitbit\OAuth\Config\Config;
use App\DataSources\Connectors\Fitbit\OAuth\Authorizator\Authorizator;
use App\DataSources\Connectors\Fitbit\OAuth\Middleware\MiddlewareFactory;
use App\DataSources\Connectors\Fitbit\OAuth\Client\Client;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;

class ServiceProvider
{
    public function build(
        TokenPersistenceInterface $tokenPersistence,
        string $clientId,
        string $clientSecret,
        string $redirectUrl
    ) {
		$config = new Config($clientId, $clientSecret, $redirectUrl);
		$authorizator = new Authorizator($config, $tokenPersistence);
		$middlewareFactory = new MiddlewareFactory($config, $tokenPersistence);
        $middlewareFactory->createOAuthMiddleware();
        $client = new Client($middlewareFactory, $authorizator);
		return new \App\DataSources\Connectors\Fitbit\Api\Fitbit($client);
    }
}
