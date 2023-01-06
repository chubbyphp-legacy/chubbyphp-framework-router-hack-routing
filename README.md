# chubbyphp-framework-router-hack-routing

[![CI](https://github.com/chubbyphp/chubbyphp-framework-router-hack-routing/workflows/CI/badge.svg?branch=master)](https://github.com/chubbyphp/chubbyphp-framework-router-hack-routing/actions?query=workflow%3ACI)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-framework-router-hack-routing/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-framework-router-hack-routing?branch=master)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/chubbyphp/chubbyphp-framework-router-hack-routing/master)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-framework-router-hack-routing/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-framework-router-hack-routing/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-framework-router-hack-routing)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-framework-router-hack-routing/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-framework-router-hack-routing)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-framework-router-hack-routing/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-framework-router-hack-routing)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-framework-router-hack-routing&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-framework-router-hack-routing)

## Description

Hack routing implementation for [chubbyphp-framework][1].

## Requirements

 * php: ^8.0
 * [chubbyphp/chubbyphp-framework][1]: ^5.0.3
 * [chubbyphp/chubbyphp-http-exception][2]: ^1.0.1
 * [azjezz/hack-routing][3]: dev-main@dev
 * [psr/http-message][4]: ^1.0.1

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-framework-router-hack-routing][10].

```bash
composer require chubbyphp/chubbyphp-framework-router-hack-routing "^1.0"
```

## Usage

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\Framework\Application;
use Chubbyphp\Framework\Middleware\ExceptionMiddleware;
use Chubbyphp\Framework\Middleware\RouteMatcherMiddleware;
use Chubbyphp\Framework\RequestHandler\CallbackRequestHandler;
use Chubbyphp\Framework\Router\HackRouting\RouteMatcher;
use Chubbyphp\Framework\Router\Route;
use Chubbyphp\Framework\Router\Routes;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

$loader = require __DIR__.'/../vendor/autoload.php';

$responseFactory = new ResponseFactory();

$app = new Application([
    new ExceptionMiddleware($responseFactory, true),
    new RouteMatcherMiddleware(new RouteMatcher(new Routes([
        Route::get('/hello/{name:[a-z]+}', 'hello', new CallbackRequestHandler(
            function (ServerRequestInterface $request) use ($responseFactory) {
                $name = $request->getAttribute('name');
                $response = $responseFactory->createResponse();
                $response->getBody()->write(sprintf('Hello, %s', $name));

                return $response;
            }
        ))
    ]))),
]);

$app->emit($app->handle((new ServerRequestFactory())->createFromGlobals()));
```

## Copyright

2023 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-framework
[2]: https://packagist.org/packages/chubbyphp/chubbyphp-http-exception
[3]: https://packagist.org/packages/azjezz/hack-routing
[4]: https://packagist.org/packages/psr/http-message
[10]: https://packagist.org/packages/chubbyphp/chubbyphp-framework-router-hack-routing
