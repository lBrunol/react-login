<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr7Middlewares\Middleware\TrailingSlash;

$app->add(function(ServerRequestInterface $request, ResponseInterface $response, callable $next){
    $response = $next($request, $response);
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});

$app->add(new TrailingSlash(false));
