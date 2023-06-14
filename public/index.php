<?php

namespace PhpProject9;

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use PhpProject9\DBConnection\Connection;

//Создаем подключение к БД


session_start();

// $container = new Container();
// $container->set('renderer', function () {
//     return new Slim\Views\PhpRenderer(__DIR__ . '/../templates');
// });

// $container->set('flash', function () {
//     return new Slim\Flash\Messages();
// });

// $container->set('connection', function () {
//     $pdo = Connection::get()->connect();
//     return $pdo;
// });
var_dump("1");

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response, $args) {
    $renderer = new PhpRenderer('../templates');
    //echo "1";
    $data = [];
    return $renderer->render($response, "index.phtml", $args);
});
$app->run();