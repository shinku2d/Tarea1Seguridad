<?php
/**
  Importación de clases y bibliotecas
*/
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require 'vendor/autoload.php';

session_start();

/**
  Configuración y lanzamiento de la biblioteca Slim
*/
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer('pages/');

/**
  Servicios de paginación
*/
$app->group('/page', function () use ($app) {
  //Carga la página de inicio de sesión
  $app->get('/login', function ($request, $response) {
      if(isset($_SESSION['usuario'])){
        $response = $response->withRedirect('/page/pago', 301);
      } else {
        $response = $this->view->render($response, 'login.phtml', []);
      }
      return $response;
  });
  //Carga la página de pagos, donde se ingresan y registran los datos de pago
  $app->get('/pago', function ($request, $response) {
    if(isset($_SESSION['usuario'])){
      $clave_hash = 'EstaEsLaClaveHash';
      $fecha = new DateTime();
      $transaccion = rand(10000000, 99999999);
      //Calculo de hash de datos, el número de transación se genera aleatoriamente
      $hash = hash('md5', $_SESSION['usuario'].'-'.'CRC'.'-'.$fecha->getTimestamp().'-'.'10000'.'-'.$transaccion.'-'.'miIDcomercio'.'-'.$clave_hash);
      $parametros = [
        'usuario' => $_SESSION['usuario'],
        'monto' => 10000,
        'moneda' => 'CRC',
        'timestamp' => $fecha->getTimestamp(),
        'transaccion' => $transaccion,
        'comercio' => 'miIDcomercio',
        'hash' => $hash,
        'url' => '/page/pago'
      ];
      $response = $this->view->render($response, 'pago.phtml', ['params' => $parametros]);
    } else {
      $response = $response->withRedirect('/page/login', 301);
    }
      return $response;
  });
  //Carga la página de respuesta de pago
  $app->get('/pago/{respuesta}', function ($request, $response, $args) {
    if(isset($_SESSION['usuario'])){
      if($args['respuesta']){
        $response = $this->view->render($response, 'pagado.phtml', ['titulo_respuesta' => 'Pago realizado', 'informacion' => 'La transación fue procesada con éxito.']);
      } else {
        $response = $this->view->render($response, 'pagado.phtml', ['titulo_respuesta' => 'Pago no realizado', 'informacion' => 'No se procesó la transacción, verifique los datos e intentelo de nuevo.']);
      }
    } else {
      $response = $response->withRedirect('/page/login', 301);
    }
    unset($_SESSION['usuario']);
    return $response;
  });
  /**
    ESTE SERVICIO IRIA EN EL SERVIDOR DEL BANCO O COMPAÑIA DE TERCERIZACIÓ
  */
  //Carga la página de la pasarela, donde van los datos de tarjeta
  $app->get('/pasarela', function ($request, $response) {
      $clave_hash = 'EstaEsLaClaveHash';
      $hash = hash('md5',
                $_SESSION['usuario'].'-'
                .$_SESSION['moneda'].'-'
                .$_SESSION['timestamp'].'-'
                .$_SESSION['monto'].'-'
                .$_SESSION['transaccion'].'-'
                .$_SESSION['comercio'].'-'
                .$clave_hash);
      if($hash == $_SESSION['hash']){
        $response = $this->view->render($response, 'pasarela.phtml', ['monto' => $_SESSION['monto'], 'moneda' => $_SESSION['moneda']]);
      } else {
        $response = $response->withRedirect($_SESSION['url'].'/0', 301);
      }
      return $response;
  });
});

/**
  Servicios
*/
$app->group('/service', function () use ($app) {
  //Servicio del login, NO ES REAL, LOGUEA DE CUALQUIER FORMA
  $app->post('/login', function ($request, $response) {
      $parsedBody = $request->getParsedBody();
      $_SESSION['usuario'] = $parsedBody['usuario'];
      return $response;
  });
  //Servicio de logout, NO ES REAL, SIEMPRE DESLOGUEA
  $app->post('/logout', function ($request, $response) {
      unset($_SESSION['usuario']);
      return $response;
  });
  //Permite crea nuevamente el hash en caso de cambios autorizados
  $app->post('/hash', function ($request, $response) {
      $clave_hash = 'EstaEsLaClaveHash';
      $parsedBody = $request->getParsedBody();
      $data = ['hash' => hash('md5',
      $_SESSION['usuario'].'-'
      .$parsedBody['moneda'].'-'
      .$parsedBody['timestamp'].'-'
      .$parsedBody['monto'].'-'
      .$parsedBody['transaccion'].'-'
      .$parsedBody['comercio'].'-'
      .$clave_hash)];
      return $response->withJson($data, 201);
  });
  /**
    ESTE SERVICIO IRIA EN EL SERVIDOR DEL BANCO O COMPAÑIA DE TERCERIZACIÓ
  */
  /* Recibe los datos del servidor de origen en los registra, en este caso
    como variables de sesión, pero trambien podrían ir a una base de datos u
    otro medio */
  $app->post('/cargar', function ($request, $response) {
      //PROCESO
      $parsedBody = $request->getParsedBody();
      $_SESSION['usuario'] = $parsedBody['usuario'];
      $_SESSION['monto'] = $parsedBody['monto'];
      $_SESSION['moneda'] = $parsedBody['moneda'];
      $_SESSION['timestamp'] = $parsedBody['timestamp'];
      $_SESSION['transaccion'] = $parsedBody['transaccion'];
      $_SESSION['comercio'] = $parsedBody['comercio'];
      $_SESSION['hash'] = $parsedBody['hash'];
      $_SESSION['url'] = $parsedBody['url'];
      $data = ['estado' => 1, 'url' => '/page/pasarela'];
      return $response->withJson($data, 201);
  });
  /**
    ESTE SERVICIO IRIA EN EL SERVIDOR DEL BANCO O COMPAÑIA DE TERCERIZACIÓ
  */
  /* Procesa la transaccion, el hash es verificado nuevamente, además verifica
    otro aspectos como los montos, los saldos y los datos de tarjeta, EN ESTE
    CASO NO ES REAL, SIEMPRE LO VALIDA. */
  $app->post('/procesar', function ($request, $response) {
      $clave_hash = 'EstaEsLaClaveHash';
      $hash = hash('md5',
                $_SESSION['usuario'].'-'
                .$_SESSION['moneda'].'-'
                .$_SESSION['timestamp'].'-'
                .$_SESSION['monto'].'-'
                .$_SESSION['transaccion'].'-'
                .$_SESSION['comercio'].'-'
                .$clave_hash);
      if($hash == $_SESSION['hash']){
        $data = ['estado' => 1, 'url' => $_SESSION['url'].'/1'];
      } else {
        $data = ['estado' => 0, 'url' => $_SESSION['url'].'/0'];
      }
      return $response->withJson($data, 201);
  });
});

$app->run();
?>
