<?php

namespace PhpProject9;

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use App\Repository;
use App\UrlCheker;
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
    $messages = $this->get('flash')->getMessages();
    $data = [
        'url' => [],
        'errors' => $messages
    ];
    return $this->get('renderer')->render($response, "index.phtml", $data);
 })->setName('main');

 $app->post('/urls', function ($request, $response) use ($router, $db) {
    $url = $request->getParsedBodyParam('url');
    // $validator = new UrlCheker();
    // var_dump($validator);
    // $errors = $validator->valudateUrl($url);
    // //Check errors of validation
    // if(isset($errors)) {
    //     $params = ['url' => $url, 'errors' => $errors];
    //     return $this->get('renderer')->render($response->withStatus(422), 'index.phtml', $params);
    // }
    $urlExsit = $db->findId($url['name']);
    if ($urlExsit) {
        $id = $urlExsit['id'];
        $this->get('flash')->addMessage('success', 'Url has been already exist');
    } else {
        $id = $db->insertUrl($url['name']);
        $this->get('flash')->addMessage('success', 'Url has been created');
    }
    return $response->withRedirect($router->urlFor('url', ['id' => $id]));
 })->setName("addUrl");

 $app->get('/urls', function ($request, $response) use ($router, $db) {
    //TODO получить урлы
    //TODO получить данные по дате последней проверки урлов
    //TODO smapit'
    $data = [
        'urls' => $db->all()
    ];
    return $this->get('renderer')->render($response, "urls.phtml", $data);
 })->setName('urls');

 $app->get('/urls/{id}', function ($request, $response, $args) use ($router, $db) {
    $messages = $this->get('flash')->getMessages();
    $foundID = $args['id'];
    $dataUrl = $db->findUrl($foundID);
    //TODO add dates of url checks
    $data = [
        'id' => $args['id'],
        'dataUrl' => $dataUrl,
        'messages' => $messages
    ];
    //var_dump($data);
    return $this->get('renderer')->render($response, "url.phtml", $data);
 })->setName('url');
 $app->run();
 //post na url check
