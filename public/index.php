<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use App\Repository;
use App\UrlChecker;
use Carbon\Carbon;
use Slim\Flash\Messages;
use DI\Container;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use DiDom\Document;
use Dotenv\createImmutable;
use Dotenv\Dotenv;

session_start();
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

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


 $app->get('/', function ($request, $response) {
    $messages = $this->get('flash')->getMessages();
    $data = [
        'url' => [],
        'errors' => $messages
    ];
    return $this->get('renderer')->render($response, "index.phtml", $data);
 })->setName('main');

 $app->post('/urls', function ($request, $response) use ($router, $db) {
    $url = $request->getParsedBodyParam('url');
    $validator = new UrlChecker();
    //var_dump($validator);
    $errors = $validator->valudateUrl($url);
    //Check errors of validation
    if (count($errors) > 0) {
        $params = ['url' => $url, 'errors' => $errors];
        return $this->get('renderer')->render($response->withStatus(422), 'index.phtml', $params);
    }
    //Check if url already exist
    $urlExsit = $db->findId($url['name']);
    if ($urlExsit) {
        $id = $urlExsit['id'];
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        $id = $db->insertUrl($url['name']);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }
    return $response->withRedirect($router->urlFor('url', ['id' => $id]));
 })->setName("addUrl");

 $app->get('/urls', function ($request, $response) use ($db) {
    $dataUrls = $db->all();
    $listUrls = [];
    foreach ($dataUrls as $url) {
        $id = $url['id'];
        $name = $url['name'];
        $lastTime = $db->findLastCheck($id);
        $created_at = $lastTime['created_at'] ?? '';
        $statusCode = $lastTime['status_code'] ?? '';
        $listUrls[] = [
            'id' => $id,
            'name' => $name,
            'status_code' => $statusCode,
            'created_at' => $created_at
        ];
    }
    $data = [
        'urls' => $listUrls
    ];
    return $this->get('renderer')->render($response, "urls.phtml", $data);
 })->setName('urls');

 $app->get('/urls/{id}', function ($request, $response, $args) use ($db) {
    $messages = $this->get('flash')->getMessages();
    $dataUrl = $db->findUrl($args['id']);
    $checks = $db->findCheckUrl($args['id']);
    $data = [
        'id' => $args['id'],
        'dataUrl' => $dataUrl,
        'messages' => $messages,
        'checks' => $checks
    ];
    //var_dump($data);
    return $this->get('renderer')->render($response, "url.phtml", $data);
 })->setName('url');

 $app->post("/urls/{id}/check", function ($request, $response, $args) use ($router, $db) {
    $urlId = $args['id'];


    $urlData = $db->findUrl($urlId);
    $url = $urlData['name'];
    //делаем запрос
    $client = new Client();
    try {
        $res = $client->request('GET', $url, ['connect_timeout' => 3.14]);
    } catch (GuzzleHttp\Exception\BadResponseException $e) { // Exception 4xx/5xx codes
        $res = $e->getResponse();
        //$this->get('flash')->addMessage('warning', 'Проверка была выполнена успешно, но сервер ответил с ошибкой');
        $this->get('flash')->addMessage('warning', 'Страница успешно проверена');

        $statusCode = $res->getStatusCode();
        $urlCheckData = $db->addCheck($urlId, $statusCode);
        return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
    } catch (GuzzleHttp\Exception\ConnectException $e) { // Exception when not connection
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
    }
    //Парсинu
    $statusCode = $res->getStatusCode();
    $html = $res->getBody()->getContents();
    $document = new Document($html);

    // Parse title
    $titleElm = $document->first('title');
    $title = '';
    if ($titleElm) {
        /** @phpstan-ignore-next-line */
        $title = $titleElm->text();
    }

    // Parse h1
    $h1Elem = $document->first('h1');
    $h1 = '';
    if ($h1Elem) {
        /** @phpstan-ignore-next-line */
        $h1 = $h1Elem->text();
    }

    $descriptionElm = $document->first('meta[name=description]');
    $description = '';
    if ($descriptionElm) {
        $description = $descriptionElm->getAttribute('content') ?? '';
    }

    $urlCheckData = $db->addCheck($urlId, $statusCode, $title, $h1, $description);
    $this->get('flash')->addMessage('success', 'Страница успешно проверена');

    return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
 })->setName("addCheck");
 $app->run();
 //post na url check
