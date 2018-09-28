<?php
// DIC configuration

$container = $app->getContainer();

//App config
$container['secretkey'] = "@!_KRS)sistema_poseidon!!";

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['errorHandler'] = function($c){
    return function($request, $response, $exception) use($c) {
        $statusCode = $exception->getCode() ? $exception->getCode() : 500;
        return $c['response']->withStatus($statusCode)->withJson(["message" => $exception->getMessage()], $statusCode);
    };
};

$container['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

$container['notAllowedHandler'] = function($c){
    return function($request, $response, $methods) use($c){
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
            ->withJson(["message" => "Method not allowed; Method must be one of: " . implode(', ', $methods)], 405);
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withJson(['message' => 'Page not found']);
    };
};