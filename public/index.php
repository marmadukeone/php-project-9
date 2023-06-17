<?php

namespace PhpProject9;

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use App\Repository;
use Carbon\Carbon;
use Slim\Flash\Messages;
use DI\Container;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


session_start();
$db = new Repository();
$container = new Container();
$container->set('renderer', function () {
     return new PhpRenderer(__DIR__ . '/../templates');
});

 $container->set('flash', function () {
     return new Messages();
 });


 AppFactory::setContainer($container);
 $app = AppFactory::create();
 $app->addErrorMiddleware(true, true, true);

 $router = $app->getRouteCollector()->getRouteParser();


 $app->get('/', function ($request, $response) use ($router, $db) {
    //$router->urlFor('urls'); // /users
    //$router->urlFor('urls', ['id' => 1]); // /users/4
    //$url = 'http://username:password@hostname:9090/path?arg=value#anchor';
    //$parseUrl = parse_url($url);
    $newid = $db->insertUrl("'http://username:password@hostname:9090/path?arg=value#anchor'");
    var_dump($newid);
    var_dump($db->all());
    //echo "1";
    $data = [];
    return $this->get('renderer')->render($response, "index.phtml", $data);
 })->setName('main');

 $app->get('/urls', function ($request, $response) {
    //echo "1";
    $data = [];
    return $this->get('renderer')->render($response, "urls.phtml", $data);
 })->setName('urls');

 $app->get('/urls/{id}', function ($request, $response, $args) {
    //echo "1";
    var_dump($args);
    $data = [
        'id' => $args['id']
    ];
    var_dump($data);
    return $this->get('renderer')->render($response, "url.phtml", $data);
 });
 $app->run();
