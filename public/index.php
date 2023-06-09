<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response, $args) {
    $renderer = new PhpRenderer('../templates');
    //echo "1";
    $data = [];
    return $renderer->render($response, "index.php", $args);
});
$app->run();