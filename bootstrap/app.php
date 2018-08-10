<?php

use FastRoute\Dispatcher;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Run;

require_once __DIR__ . '/../vendor/autoload.php';

/*
* Request instance (use this instead of $_GET, $_POST, etc).
*/
$request = Request::createFromGlobals();

/*
* Dotenv initialization
*/
if (file_exists(__DIR__ . '/../.env') !== true) {
  Response::create('Missing .env file.', Response::HTTP_INTERNAL_SERVER_ERROR)
  ->prepare($request)
  ->send();
  return;
}
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

/*
* Container setup
*/
$container = new Container();
/*
* PDO
*/

$container
    ->add('PDO')
    ->withArgument(getenv('DB_CONN'))
    ->withArgument(getenv('DB_USER'))
    ->withArgument(getenv('DB_PASS'))
    ->withArgument(array(PDO::ATTR_PERSISTENT => TRUE));

$container->delegate( new ReflectionContainer());

/*
* Routes
*/
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
  $routes = require __DIR__ . '/routes.php';
  foreach ($routes as $route) {
    $cotrollers = $route[2];
    $cotrollers[0] = 'Vanila\Controllers\\' . $cotrollers[0];

    $r->addRoute($route[0],  $route[1], $cotrollers);
  }
});


/*
* Dispatch
*/
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
switch ($routeInfo[0]) {
  case Dispatcher::NOT_FOUND:
      // No matching route was found.
      Response::create("404 Not Found", Response::HTTP_NOT_FOUND)
      ->prepare($request)
      ->send();
      break;
  case Dispatcher::METHOD_NOT_ALLOWED:
      // A matching route was found, but the wrong HTTP method was used.
      Response::create("405 Method Not Allowed", Response::HTTP_METHOD_NOT_ALLOWED)
      ->prepare($request)
      ->send();
      break;
  case Dispatcher::FOUND:
      // Fully qualified class name of the controller
      $fqcn = $routeInfo[1][0];

      // Controller method responsible for handling the request
      $routeMethod = $routeInfo[1][1];
      $routeParams = $routeInfo[2];

      // Obtain an instance of route's controller
      // Resolves constructor dependencies using the container

      $controller = $container->get($fqcn);

      // Generate a response by invoking the appropriate route method in the controller
      $request->all = json_decode( file_get_contents( 'php://input' ), true );
      $request->params = $routeParams;

      $response = new Response($controller->$routeMethod($request));

      if ($response instanceof Response) {
        // Send the generated response back to the user
        $response
        ->prepare($request)
        ->send();
      }
      break;
  default:
      // According to the dispatch(..) method's documentation this shouldn't happen.
      // But it's here anyways just to cover all of our bases.
      Response::create('Received unexpected response from dispatcher.', Response::HTTP_INTERNAL_SERVER_ERROR)
      ->prepare($request)
      ->send();
      return;
}
