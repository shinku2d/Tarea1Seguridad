<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

session_start();

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '';
$config['db']['dbname'] = 'page';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer('pages/');

$app->group('/page', function () use ($app) {
    $app->get('/login', function ($request, $response) {
        if(isset($_SESSION['usuario'])){
          $response = $response->withRedirect('/page/pago', 301);
        } else {
          $response = $this->view->render($response, 'login.phtml', []);
        }
        return $response;
    });
    $app->get('/pago', function ($request, $response) {
      if(isset($_SESSION['usuario'])){
        $parametros = ['usuario' => $_SESSION['usuario'], 'monto' => 10000, 'moneda' => 'CRC'];
        $response = $this->view->render($response, 'pago.phtml', ['params' => $parametros]);
      } else {
        $response = $response->withRedirect('/page/login', 301);
      }
        return $response;
    });
    $app->get('/pasarela', function ($request, $response) {
        $parametros = ['usuario' => $_SESSION['usuario'], 'monto' => 10000, 'moneda' => 'CRC'];
        $response = $this->view->render($response, 'pasarela.phtml', ['params' => $parametros]);
        return $response;
    });
});

//Se debe cambiar para que retorne datos correctos
$app->group('/service', function () use ($app) {
    $app->post('/login', function ($request, $response) {
        $parsedBody = $request->getParsedBody();
        $_SESSION['usuario'] = $parsedBody['usuario'];
        return $response;
    });
    $app->post('/logout', function ($request, $response) {
        unset($_SESSION['usuario']);
        return $response;
    });
    $app->post('/pagar', function ($request, $response) {
        //PROCESO
        $data = ['url' => '/page/pasarela'];
        return $response->withJson($data, 201);
    });
});

/*
$app->get('/{name}/{data}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $data = $args['data'];
    $response->getBody()->write("Hello, $name - $data");

    //$response = $this->view->render($response, 'tickets.phtml', ['tickets' => $tickets]);
    return $response;
});

$app->get('/tickets', function (Request $request, Response $response) {
    //$this->logger->addInfo("Ticket list");
    //$mapper = new TicketMapper($this->db);
    $tickets = array("one", "two", "tree");//$mapper->getTickets();

    $response->getBody()->write(var_export($tickets, true));
    return $response;
});
*/

$app->run();
?>
